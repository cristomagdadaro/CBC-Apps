<script setup>
import { ref, onMounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ActionHeaderLayout from '@/Layouts/ActionHeaderLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ActionMessage from '@/Components/ActionMessage.vue';

const props = defineProps({
    paginator: Object,
});

const page = usePage();

const form = useForm({
    image: null,
});

const imagePreview = ref(null);
const canvasRef = ref(null);
const fileInput = ref(null);

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
        
        // Reset canvas
        if (canvasRef.value) {
            const ctx = canvasRef.value.getContext('2d');
            ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
        }
    }
};

const analyze = () => {
    form.post(route('pollen_analysis.analyze'), {
        preserveScroll: true,
        onSuccess: () => {
            const result = page.props.flash?.inference_result;
            if (result && result.boxes) {
                drawBoxes(result.boxes);
            }
            form.reset('image');
        },
    });
};

const drawBoxes = (boxes) => {
    if (!canvasRef.value || !imagePreview.value) return;
    
    const img = new Image();
    img.src = imagePreview.value;
    img.onload = () => {
        const canvas = canvasRef.value;
        const ctx = canvas.getContext('2d');
        
        // Match canvas size to image display size
        canvas.width = img.naturalWidth;
        canvas.height = img.naturalHeight;
        
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.lineWidth = 3;
        ctx.strokeStyle = '#00FF00'; // Green boxes
        ctx.fillStyle = 'rgba(0, 255, 0, 0.2)';
        
        boxes.forEach(box => {
            const [x1, y1, x2, y2] = box;
            const width = x2 - x1;
            const height = y2 - y1;
            ctx.strokeRect(x1, y1, width, height);
            ctx.fillRect(x1, y1, width, height);
        });
    };
};

// Re-draw if there's a result in flash on mount
onMounted(() => {
    if (page.props.flash?.inference_result) {
        drawBoxes(page.props.flash.inference_result.boxes);
    }
});
</script>

<template>
    <AppLayout title="Pollen Analysis">
        <template #header>
            <ActionHeaderLayout 
                title="Pollen Analysis (YOLO AI)" 
                subtitle="Upload microscope images to automatically count pollen grains using the i25 AI model."
            />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Upload Section -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="analyze" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Image</label>
                            <input 
                                type="file" 
                                @change="handleFileChange" 
                                ref="fileInput"
                                accept="image/*"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900 dark:file:text-indigo-200"
                            />
                            <InputError :message="form.errors.image" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing || !form.image">
                                {{ form.processing ? 'Analyzing...' : 'Analyze Image' }}
                            </PrimaryButton>
                            
                            <ActionMessage :on="form.recentlySuccessful" class="mr-3">
                                <span class="text-green-600 font-bold dark:text-green-400">
                                    {{ page.props.flash?.success }}
                                </span>
                            </ActionMessage>
                            <ActionMessage :on="!!page.props.flash?.error" class="mr-3">
                                <span class="text-red-600 font-bold dark:text-red-400">
                                    {{ page.props.flash?.error }}
                                </span>
                            </ActionMessage>
                        </div>
                    </form>

                    <!-- Preview and Canvas -->
                    <div v-show="imagePreview" class="mt-6 relative inline-block border rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-900">
                        <img :src="imagePreview" class="max-w-full h-auto block" alt="Preview" />
                        <canvas 
                            ref="canvasRef" 
                            class="absolute top-0 left-0 w-full h-full pointer-events-none"
                        ></canvas>
                    </div>
                </div>
                
                <!-- History Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Analysis History</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Image</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pollen Count</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Inference Time</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="item in paginator.data" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ new Date(item.created_at).toLocaleString() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a :href="route('pollen_analysis.image', item.id)" target="_blank" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">View Image</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-gray-100">
                                        {{ item.pollen_count }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ item.inference_time_ms ? Math.round(item.inference_time_ms) + ' ms' : 'N/A' }}
                                    </td>
                                </tr>
                                <tr v-if="paginator.data.length === 0">
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No history found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
