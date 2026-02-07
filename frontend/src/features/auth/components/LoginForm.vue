<script setup lang="ts">
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useForm } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import { toast } from "vue-sonner";
import { useAuthStore } from "../stores/authStore";
import { useUserStore } from "../stores/userStore";
import { authApi } from "../api/authApi";
import { loginSchema } from "@/lib/validation/schemas/auth";
import { getApiErrorMessage } from "@/lib/api-error";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Checkbox } from "@/components/ui/checkbox";
import { EyeIcon, EyeOffIcon, Loader2 } from "lucide-vue-next";
import TermsAndConditionsDialog from "./TermsAndConditionsDialog.vue";

const router = useRouter();
const authStore = useAuthStore();
const userStore = useUserStore();

const showPassword = ref(false);
const showTerms = ref(false);
const apiError = ref<string>("");

const { handleSubmit, errors, isSubmitting, defineField } = useForm({
  validationSchema: toTypedSchema(loginSchema),
  initialValues: {
    email: "",
    password: "",
    rememberMe: true,
  },
});

const [email, emailAttrs] = defineField("email");
const [password, passwordAttrs] = defineField("password");
const [rememberMe, rememberMeAttrs] = defineField("rememberMe");

const onSubmit = handleSubmit(async (values) => {
  apiError.value = "";

  try {
    const response = await authApi.login({
      email: values.email,
      password: values.password,
      rememberMe: values.rememberMe,
    });

    // Store authentication tokens in authStore
    authStore.setTokens(response.accessToken, response.expiresIn, values.rememberMe);

    // Store user data in userStore
    if (response.user) {
      userStore.updateUser(response.user);
    }

    // Show success toast
    toast.success("Login successful");

    // Redirect to dashboard
    router.push("/dashboard");
  } catch (err: any) {
    const errorMessage = getApiErrorMessage(err, "Invalid credentials. Please try again.");
    apiError.value = errorMessage;
    toast.error(errorMessage);
  }
});
</script>

<template>
  <div
    class="w-full bg-white p-6 sm:p-10 rounded-2xl shadow-xl lg:shadow-2xl border border-slate-100"
  >
    <div class="mb-10 text-center lg:text-left">
      <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
        Welcome back
      </h1>
      <p class="text-slate-500 mt-3 text-base">
        Enter your credentials to access your dashboard.
      </p>
    </div>

    <form @submit.prevent="onSubmit" class="space-y-6">
      <!-- Email -->
      <div class="space-y-2">
        <Label for="email" class="text-sm font-semibold text-slate-700"
          >Email Address</Label
        >
        <Input
          id="email"
          v-model="email"
          v-bind="emailAttrs"
          type="email"
          placeholder="your.email@example.com"
          :disabled="isSubmitting"
          :class="{ 'border-destructive': errors.email }"
          class="h-11 border-slate-200 bg-slate-50/50 focus:bg-white transition-colors"
        />
        <p v-if="errors.email" class="text-sm text-destructive">
          {{ errors.email }}
        </p>
      </div>

      <!-- Password -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <Label for="password" class="text-sm font-semibold text-slate-700"
            >Password</Label
          >
        </div>
        <div class="relative">
          <Input
            id="password"
            v-model="password"
            v-bind="passwordAttrs"
            :type="showPassword ? 'text' : 'password'"
            placeholder="••••••••"
            :disabled="isSubmitting"
            :class="{ 'border-destructive': errors.password }"
            class="h-11 pr-11 border-slate-200 bg-slate-50/50 focus:bg-white transition-colors"
          />
          <button
            type="button"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none transition-colors"
            @click="showPassword = !showPassword"
          >
            <EyeOffIcon v-if="showPassword" class="h-5 w-5" />
            <EyeIcon v-else class="h-5 w-5" />
          </button>
        </div>
        <p v-if="errors.password" class="text-sm text-destructive">
          {{ errors.password }}
        </p>
      </div>

      <!-- Remember Me -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Checkbox
              id="rememberMe"
              v-model="rememberMe"
              v-bind="rememberMeAttrs"
              :disabled="isSubmitting"
            />
            <Label
              for="rememberMe"
              class="text-sm font-medium text-slate-700 cursor-pointer"
            >
              Remember Me
            </Label>
          </div>
        </div>
      </div>

      <!-- Error message -->
      <div
        v-if="apiError"
        class="bg-red-50 text-red-600 p-4 rounded-xl text-sm border border-red-100 font-medium"
      >
        {{ apiError }}
      </div>

      <!-- Submit button -->
      <Button
        type="submit"
        :disabled="isSubmitting"
        class="w-full h-11 text-base font-bold bg-slate-900 hover:bg-slate-800 transition-all shadow-md hover:shadow-lg active:scale-[0.99] rounded-xl"
      >
        <template v-if="isSubmitting">
          <Loader2 class="mr-2 h-4 w-4 animate-spin" />
          Signing in...
        </template>
        <span v-else>Sign In</span>
      </Button>

      <!-- Terms and Conditions link -->
      <div class="text-center pt-2">
        <button
          type="button"
          class="text-xs text-slate-400 hover:text-teal-600 hover:underline transition-colors font-medium"
          @click="showTerms = true"
        >
          Terms and Conditions of Use
        </button>
      </div>
    </form>

    <TermsAndConditionsDialog v-model:open="showTerms" />
  </div>
</template>
