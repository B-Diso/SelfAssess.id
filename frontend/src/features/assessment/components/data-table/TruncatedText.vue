<script setup lang="ts">
import { ref, onMounted, onUpdated, onUnmounted, nextTick } from "vue";

defineProps<{
  text: string | null | undefined;
  className?: string;
}>();

const textRef = ref<HTMLSpanElement | null>(null);
const isTruncated = ref(false);
const showTooltip = ref(false);

let resizeObserver: ResizeObserver | null = null;

const checkTruncation = () => {
  nextTick(() => {
    const el = textRef.value;
    if (!el) {
      isTruncated.value = false;
      return;
    }
    // Check if text is truncated by comparing scrollWidth with clientWidth
    // Add small buffer for subpixel rendering
    isTruncated.value = el.scrollWidth > el.clientWidth + 1;
  });
};

const handleMouseEnter = () => {
  // Recheck on hover in case window resized
  checkTruncation();
  if (isTruncated.value) {
    showTooltip.value = true;
  }
};

const handleMouseLeave = () => {
  showTooltip.value = false;
};

onMounted(() => {
  // Initial check after DOM is ready
  checkTruncation();
  
  // Use ResizeObserver for more reliable detection
  if (window.ResizeObserver && textRef.value) {
    resizeObserver = new ResizeObserver(() => {
      checkTruncation();
    });
    resizeObserver.observe(textRef.value);
  } else {
    // Fallback to window resize
    window.addEventListener('resize', checkTruncation);
  }
  
  // Check again after a short delay to ensure layout is stable
  setTimeout(checkTruncation, 100);
});

onUpdated(() => {
  checkTruncation();
});

onUnmounted(() => {
  if (resizeObserver) {
    resizeObserver.disconnect();
  } else {
    window.removeEventListener('resize', checkTruncation);
  }
});
</script>

<template>
  <div class="relative min-w-0 w-full">
    <span 
      ref="textRef" 
      :class="[className || '', 'truncate block w-full']"
      @mouseenter="handleMouseEnter"
      @mouseleave="handleMouseLeave"
    >
      {{ text || "-" }}
    </span>
    <!-- Custom tooltip - only shows when truncated -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-if="showTooltip && isTruncated && text"
        class="absolute z-50 bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1.5 bg-slate-900 text-white text-xs rounded-md shadow-lg whitespace-nowrap max-w-xs"
      >
        <span class="block whitespace-normal break-words max-w-[300px] leading-relaxed">{{ text }}</span>
        <!-- Arrow -->
        <div class="absolute top-full left-1/2 -translate-x-1/2 -mt-1 border-4 border-transparent border-t-slate-900"></div>
      </div>
    </Transition>
  </div>
</template>
