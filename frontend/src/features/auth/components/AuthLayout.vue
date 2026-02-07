<template>
  <div class="h-screen flex bg-white font-sans">
    <!-- Left Side: Login Form -->
    <div
      class="w-full lg:w-1/2 flex flex-col justify-center px-4 sm:px-6 lg:px-20 xl:px-24 overflow-hidden"
    >
      <div class="mx-auto w-full max-w-md">
        <slot />
      </div>
    </div>

    <!-- Right Side: Hero Section (Branding) -->
    <div class="hidden lg:flex lg:w-1/2 relative flex-1">
      <div
        class="absolute inset-0 h-full w-full overflow-hidden"
        style="background-color: #0f172a"
      >
        <!-- Decorative Background Patterns -->
        <div
          class="absolute inset-0 bg-linear-to-br from-teal-500/20 via-[#0f172a] to-[#0f172a] z-10"
        ></div>
        <div
          class="absolute top-0 right-0 -mt-20 -mr-20 w-[500px] h-[500px] bg-teal-500/10 rounded-full blur-[120px]"
        ></div>
        <div
          class="absolute bottom-0 left-0 -mb-20 -ml-20 w-[500px] h-[500px] bg-emerald-500/10 rounded-full blur-[120px]"
        ></div>

        <!-- Content -->
        <div
          class="absolute inset-0 z-20 flex flex-col items-center justify-center p-12 text-center"
        >
          <div
            class="mb-12 p-6 rounded-3xl bg-white/10 backdrop-blur-sm border border-teal-400/20 shadow-2xl"
          >
            <img
              src="/SelfAssess.id.png"
              alt="SelfAssess.id Logo"
              class="h-40 w-auto z-30 rounded-2xl"
            />
          </div>
          <h2
            class="text-4xl font-extrabold text-white tracking-tight sm:text-5xl"
          >
            Master Your Compliance
          </h2>
          <p
            class="mt-6 max-w-md text-lg text-slate-300 leading-relaxed font-medium"
          >
            Streamline your quality assurance processes, manage standards with
            precision, and drive organizational excellence with
            <span class="text-teal-400 font-bold italic">SelfAssess.id</span>.
          </p>

          <!-- Feature highlights -->
          <div class="mt-12 grid grid-cols-2 gap-6 text-left">
            <div class="flex items-start gap-3">
              <div class="mt-1 p-1 bg-teal-500/20 rounded-md">
                <div class="w-1.5 h-1.5 bg-teal-400 rounded-full"></div>
              </div>
              <p
                class="text-xs font-semibold text-slate-300 uppercase tracking-widest"
              >
                Global Standards
              </p>
            </div>
            <div class="flex items-start gap-3">
              <div class="mt-1 p-1 bg-teal-500/20 rounded-md">
                <div class="w-1.5 h-1.5 bg-teal-400 rounded-full"></div>
              </div>
              <p
                class="text-xs font-semibold text-slate-300 uppercase tracking-widest"
              >
                Real-time Insights
              </p>
            </div>
          </div>
        </div>

        <!-- Footer watermark/credits -->
        <div class="absolute bottom-8 right-8 z-20">
          <p
            class="text-xs text-slate-500 font-semibold tracking-widest uppercase"
          >
            MIT License - Open Source Project
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
/**
 * Modern Split-Screen Auth Layout
 * Used primarily for the Login Page to provide a premium branding experience.
 */
</script>
