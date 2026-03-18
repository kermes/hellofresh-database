@props([
    'wireModel' => null,
    'value'     => '',
    'placeholder' => '',
])

@once
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css">
  <script src="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js" defer></script>
  <style>
    /* ── Light mode ───────────────────────────────── */
    .ql-toolbar.ql-snow {
      border-color: var(--color-zinc-300, #d1d5db);
      border-radius: 0.375rem 0.375rem 0 0;
      background: var(--color-white, #fff);
    }
    .ql-container.ql-snow {
      border-color: var(--color-zinc-300, #d1d5db);
      border-radius: 0 0 0.375rem 0.375rem;
      font-size: 0.875rem;
    }
    .ql-editor {
      min-height: 5rem;
      font-family: inherit;
    }
    .ql-editor p { margin: 0; }

    /* ── Dark mode ────────────────────────────────── */
    .dark .ql-toolbar.ql-snow {
      border-color: var(--color-zinc-600, #52525b);
      background: var(--color-zinc-800, #27272a);
    }
    .dark .ql-toolbar.ql-snow .ql-stroke { stroke: #d4d4d8; }
    .dark .ql-toolbar.ql-snow .ql-fill  { fill:  #d4d4d8; }
    .dark .ql-toolbar.ql-snow .ql-picker-label { color: #d4d4d8; }
    .dark .ql-toolbar.ql-snow button:hover .ql-stroke,
    .dark .ql-toolbar.ql-snow .ql-active .ql-stroke { stroke: #fff; }
    .dark .ql-toolbar.ql-snow button:hover .ql-fill,
    .dark .ql-toolbar.ql-snow .ql-active .ql-fill  { fill:  #fff; }

    .dark .ql-container.ql-snow {
      border-color: var(--color-zinc-600, #52525b);
      background: var(--color-zinc-900, #18181b);
      color: #f4f4f5;
    }
    .dark .ql-editor.ql-blank::before { color: #71717a; }
  </style>

  <script>
    function htmlEditor(wireModel, initialValue, placeholder) {
      return {
        editor: null,
        _debounce: null,
        _destroyed: false,

        init() {
          const boot = () => {
            if (this._destroyed) return;
            if (typeof Quill === 'undefined') {
              return setTimeout(boot, 50);
            }

            this.editor = new Quill(this.$refs.quillEditor, {
              theme: 'snow',
              placeholder: placeholder,
              modules: {
                toolbar: [
                  ['bold', 'italic', 'underline'],
                  [{ list: 'ordered' }, { list: 'bullet' }],
                  ['clean'],
                ],
              },
            });

            if (initialValue) {
              const delta = this.editor.clipboard.convert({ html: initialValue });
              this.editor.setContents(delta, 'silent');
            }

            this.editor.on('text-change', () => {
              if (this._destroyed) return;
              clearTimeout(this._debounce);
              this._debounce = setTimeout(() => {
                if (this._destroyed || !this.editor) return;
                const html = this.editor.getSemanticHTML();
                this.$wire.set(wireModel, html === '<p></p>' ? '' : html);
              }, 300);
            });
          };

          boot();
        },

        destroy() {
          this._destroyed = true;
          clearTimeout(this._debounce);
          if (this.editor) {
            this.editor.off('text-change');
            this.editor = null;
          }
        },
      };
    }
  </script>
@endonce

<div
  wire:ignore
  x-data="htmlEditor(@js($wireModel), @js($value), @js($placeholder))"
>
  <div x-ref="quillEditor"></div>
</div>
