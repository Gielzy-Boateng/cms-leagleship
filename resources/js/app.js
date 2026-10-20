import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';

// PHP's upload_max_filesize defaults to 2M on many hosts and cannot be raised from app code,
// so anything above this budget is re-encoded in the browser before it reaches Livewire.
const UPLOAD_BYTE_BUDGET = 1_500_000;
const UPLOAD_MAX_EDGE = 1920;

function encode(canvas, quality) {
    return new Promise((resolve) => {
        // WebP keeps transparency and is accepted by the server; JPEG is the fallback.
        canvas.toBlob((webp) => {
            if (webp && webp.type === 'image/webp') {
                resolve(webp);

                return;
            }

            canvas.toBlob(resolve, 'image/jpeg', quality);
        }, 'image/webp', quality);
    });
}

async function optimiseForUpload(file) {
    const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
    const scale = Math.min(1, UPLOAD_MAX_EDGE / Math.max(bitmap.width, bitmap.height));
    const width = Math.max(1, Math.round(bitmap.width * scale));
    const height = Math.max(1, Math.round(bitmap.height * scale));
    const canvas = document.createElement('canvas');

    canvas.width = width;
    canvas.height = height;
    canvas.getContext('2d').drawImage(bitmap, 0, 0, width, height);
    bitmap.close?.();

    let blob = await encode(canvas, 0.82);

    if (blob && blob.size > UPLOAD_BYTE_BUDGET) {
        blob = await encode(canvas, 0.6);
    }

    if (! blob || blob.size > file.size) {
        return file;
    }

    const name = (file.name.replace(/\.[^.]+$/, '') || 'image') + (blob.type === 'image/webp' ? '.webp' : '.jpg');

    return new File([blob], name, { type: blob.type, lastModified: Date.now() });
}

function notify(message) {
    window.dispatchEvent(new CustomEvent('toast', { detail: { message } }));
}

document.addEventListener('alpine:init', () => {
    Alpine.data('richText', (wire, model, initialHtml) => {
        // Kept outside the reactive data object: ProseMirror must not be proxied by Alpine.
        let editor = null;

        return {
            tick: 0,
            linkUrl: '',
            showLink: false,

            init() {
                editor = new Editor({
                    element: this.$refs.editable,
                    extensions: [
                        StarterKit.configure({
                            heading: { levels: [2, 3] },
                            link: { openOnClick: false, autolink: true },
                        }),
                    ],
                    content: initialHtml || '',
                    editorProps: {
                        attributes: {
                            class: 'rich-text__content',
                            id: model,
                        },
                    },
                    onTransaction: () => {
                        this.tick++;
                    },
                    onUpdate: () => {
                        wire.set(model, editor.getHTML(), false);
                    },
                });
            },

            destroy() {
                editor?.destroy();
                editor = null;
            },

            run(command, ...args) {
                editor?.chain().focus()[command](...args).run();
            },

            isActive(name, attributes) {
                // Reading `tick` makes Alpine re-evaluate bindings on every ProseMirror transaction.
                this.tick;

                return editor?.isActive(name, attributes) ?? false;
            },

            canUndo() {
                this.tick;

                return editor?.can().undo() ?? false;
            },

            canRedo() {
                this.tick;

                return editor?.can().redo() ?? false;
            },

            currentHeading() {
                this.tick;

                if (! editor) {
                    return 'p';
                }

                if (editor.isActive('heading', { level: 2 })) {
                    return 'h2';
                }

                return editor.isActive('heading', { level: 3 }) ? 'h3' : 'p';
            },

            setHeading(heading) {
                if (heading === 'h2') {
                    this.run('toggleHeading', { level: 2 });
                } else if (heading === 'h3') {
                    this.run('toggleHeading', { level: 3 });
                } else {
                    this.run('setParagraph');
                }
            },

            openLink() {
                this.linkUrl = editor?.getAttributes('link').href ?? '';
                this.showLink = true;
            },

            applyLink() {
                const url = this.linkUrl.trim();

                if (url === '') {
                    this.removeLink();

                    return;
                }

                editor?.chain().focus().extendMarkRange('link').setLink({ href: url }).run();

                this.showLink = false;
                this.linkUrl = '';
            },

            removeLink() {
                editor?.chain().focus().extendMarkRange('link').unsetLink().run();

                this.showLink = false;
                this.linkUrl = '';
            },
        };
    });

    Alpine.data('imageUpload', (wire, model) => ({
        busy: false,

        async pick(file) {
            if (! file) {
                return;
            }

            this.busy = true;

            try {
                wire.upload(
                    model,
                    file.size > UPLOAD_BYTE_BUDGET ? await optimiseForUpload(file) : file,
                    () => { this.busy = false; },
                    () => {
                        this.busy = false;
                        notify('Upload failed. Try a smaller image.');
                    },
                );
            } catch {
                this.busy = false;
                notify('Could not read that image. Try a different file.');
            }
        },
    }));
});
