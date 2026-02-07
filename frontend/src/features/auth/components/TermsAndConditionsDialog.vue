<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";

defineProps<{
  open: boolean;
}>();

defineEmits<{
  (e: "update:open", value: boolean): void;
}>();
</script>

<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="sm:max-w-[700px] h-[80vh] flex flex-col p-0 gap-0">
      <DialogHeader class="p-6 pb-2">
        <DialogTitle>Terms and Conditions of Use</DialogTitle>
        <DialogDescription>
          Please read this important notice regarding the use of this
          application.
        </DialogDescription>
      </DialogHeader>

      <div class="flex-1 overflow-y-auto p-6 pt-2">
        <div class="space-y-6 text-sm leading-relaxed">
          <!-- Bahasa Indonesia -->
          <section>
            <h3 class="font-bold text-base mb-2 border-b pb-1">
              Bahasa Indonesia (Penting)
            </h3>
            <div class="space-y-4">
              <p
                class="font-semibold text-yellow-700 bg-yellow-50 p-3 rounded-md border border-yellow-200 uppercase tracking-tight text-xs"
              >
                PERINGATAN: APLIKASI INI DALAM TAHAP ALPHA DAN PILOTING. JANGAN
                GUNAKAN DATA SENSITIF ATAU DATA RAHASIA NEGARA.
              </p>
              <p>
                1. <strong>Tujuan Piloting:</strong> Aplikasi SelfAssess.id saat
                ini sedang dalam tahap Alpha dan Piloting untuk pengembangan
                platform self-assessment multi-standar.
              </p>
              <p>
                2. <strong>Keamanan Data:</strong> Harap diperhatikan bahwa
                sistem ini masih dalam pengembangan. Dilarang keras mengunggah,
                memasukkan, atau menyimpan data yang bersifat sensitif, rahasia
                negara, data pribadi yang dilindungi secara hukum, atau
                informasi lain yang dapat membahayakan integritas organisasi
                jika terjadi kebocoran data.
              </p>
              <p>
                3. <strong>Tanggung Jawab Pengguna:</strong> Pengguna
                bertanggung jawab penuh atas segala informasi yang dimasukkan ke
                dalam sistem. Tim pengembang tidak bertanggung jawab atas
                penyalahgunaan atau kerugian yang timbul dari penggunaan sistem
                ini selama masa piloting.
              </p>
              <p>
                4. <strong>Perubahan Sistem:</strong> Sistem dapat berubah
                sewaktu-waktu tanpa pemberitahuan sebelumnya, termasuk
                penghapusan data secara berkala untuk tujuan pemeliharaan dan
                pengujian.
              </p>
            </div>
          </section>

          <!-- English -->
          <section>
            <h3 class="font-bold text-base mb-2 border-b pb-1">
              English (Important)
            </h3>
            <div class="space-y-4">
              <p
                class="font-semibold text-yellow-700 bg-yellow-50 p-3 rounded-md border border-yellow-200 uppercase tracking-tight text-xs"
              >
                NOTICE: THIS APPLICATION IS IN ALPHA AND PILOTING STAGE. DO NOT
                USE SENSITIVE OR CONFIDENTIAL GOVERNMENT DATA.
              </p>
              <p>
                1. <strong>Piloting Purpose:</strong> SelfAssess.id is currently
                in the Alpha and Piloting stage for the development of a
                multi-standard self-assessment platform.
              </p>
              <p>
                2. <strong>Data Security:</strong> Please be advised that this
                system is under active development. It is strictly prohibited to
                upload, enter, or store sensitive data, state secrets, legally
                protected personal data, or any other information that could
                jeopardize organizational integrity in the event of a data
                breach.
              </p>
              <p>
                3. <strong>User Responsibility:</strong> Users are fully
                responsible for any information entered into the system. The
                development team is not liable for any misuse or damages arising
                from the use of this system during the piloting period.
              </p>
              <p>
                4. <strong>System Changes:</strong> The system may change at any
                time without prior notice, including the periodic deletion of
                data for maintenance and testing purposes.
              </p>
            </div>
          </section>
        </div>
      </div>

      <div class="p-4 border-t bg-gray-50 flex justify-end">
        <Button @click="$emit('update:open', false)">Close / Tutup</Button>
      </div>
    </DialogContent>
  </Dialog>
</template>
