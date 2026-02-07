<script setup lang="ts">
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'
import Underline from '@tiptap/extension-underline'
import { Toggle } from '@/components/ui/toggle'
import DOMPurify from 'dompurify'
import {
  Bold,
  Italic,
  UnderlineIcon,
  List,
  ListOrdered,
  Link as LinkIcon,
} from 'lucide-vue-next'
import { watch } from 'vue'

interface Props {
  modelValue?: string
  placeholder?: string
  editable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  placeholder: 'Enter text...',
  editable: true,
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const editor = useEditor({
  extensions: [
    StarterKit,
    Link.configure({
      openOnClick: false,
      HTMLAttributes: {
        target: '_blank',
        rel: 'noopener noreferrer',
      },
    }),
    Underline,
  ],
  content: props.modelValue,
  editable: props.editable,
  onUpdate: ({ editor }) => {
    const html = editor.getHTML()
    // Sanitize HTML output before emitting
    const sanitized = DOMPurify.sanitize(html, {
      ALLOWED_TAGS: ['p', 'strong', 'em', 'u', 'ul', 'ol', 'li', 'a', 'br'],
      ALLOWED_ATTR: ['href', 'target', 'rel'],
    })
    emit('update:modelValue', sanitized)
  },
})

// Watch for external changes to modelValue
watch(
  () => props.modelValue,
  (newValue) => {
    if (editor.value) {
      const currentContent = editor.value.getHTML()
      if (currentContent !== newValue) {
        editor.value.commands.setContent(newValue || '')
      }
    }
  }
)

// Watch for editable prop changes
watch(
  () => props.editable,
  (newValue) => {
    if (editor.value) {
      editor.value.setEditable(newValue)
    }
  }
)

const setLink = () => {
  if (!editor.value) return

  const previousUrl = editor.value.getAttributes('link').href
  const url = window.prompt('Enter URL:', previousUrl)

  if (url === null) return

  if (url === '') {
    editor.value.chain().focus().extendMarkRange('link').unsetLink().run()
    return
  }

  editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}
</script>

<template>
  <div class="border rounded-md bg-background">
    <!-- Toolbar -->
    <div v-if="editor && editable" class="flex items-center gap-1 p-2 border-b bg-muted/30">
      <Toggle
        size="sm"
        :pressed="editor.isActive('bold')"
        @click="editor.chain().focus().toggleBold().run()"
        aria-label="Toggle bold"
      >
        <Bold class="h-4 w-4" />
      </Toggle>

      <Toggle
        size="sm"
        :pressed="editor.isActive('italic')"
        @click="editor.chain().focus().toggleItalic().run()"
        aria-label="Toggle italic"
      >
        <Italic class="h-4 w-4" />
      </Toggle>

      <Toggle
        size="sm"
        :pressed="editor.isActive('underline')"
        @click="editor.chain().focus().toggleUnderline().run()"
        aria-label="Toggle underline"
      >
        <UnderlineIcon class="h-4 w-4" />
      </Toggle>

      <div class="w-px h-6 bg-border mx-1" />

      <Toggle
        size="sm"
        :pressed="editor.isActive('bulletList')"
        @click="editor.chain().focus().toggleBulletList().run()"
        aria-label="Toggle bullet list"
      >
        <List class="h-4 w-4" />
      </Toggle>

      <Toggle
        size="sm"
        :pressed="editor.isActive('orderedList')"
        @click="editor.chain().focus().toggleOrderedList().run()"
        aria-label="Toggle ordered list"
      >
        <ListOrdered class="h-4 w-4" />
      </Toggle>

      <div class="w-px h-6 bg-border mx-1" />

      <Toggle
        size="sm"
        :pressed="editor.isActive('link')"
        @click="setLink"
        aria-label="Insert link"
      >
        <LinkIcon class="h-4 w-4" />
      </Toggle>
    </div>

    <!-- Editor Content -->
    <EditorContent
      :editor="editor"
      class="prose prose-sm max-w-none p-3 min-h-[120px] max-h-[300px] overflow-y-auto focus:outline-none"
    />
  </div>
</template>

<style>
/* Tiptap Editor Styles */
.ProseMirror {
  outline: none;
}

.ProseMirror p.is-editor-empty:first-child::before {
  color: #adb5bd;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}

.ProseMirror ul,
.ProseMirror ol {
  padding-left: 1.5rem;
  margin: 0.5rem 0;
}

.ProseMirror ul {
  list-style-type: disc;
}

.ProseMirror ol {
  list-style-type: decimal;
}

.ProseMirror li {
  margin: 0.25rem 0;
}

.ProseMirror a {
  color: hsl(var(--primary));
  text-decoration: underline;
  cursor: pointer;
}

.ProseMirror a:hover {
  color: hsl(var(--primary) / 0.9);
}

.ProseMirror strong {
  font-weight: 600;
}

.ProseMirror em {
  font-style: italic;
}

.ProseMirror u {
  text-decoration: underline;
}
</style>
