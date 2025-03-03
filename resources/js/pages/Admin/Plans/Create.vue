<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { useForm as useVeeForm } from 'vee-validate';
import * as yup from 'yup';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import draggable from 'vuedraggable';

interface Props {
    preset?: string;
}

const props = defineProps<Props>();
const toast = useToast();

// Form validation schema
const validationSchema = yup.object({
    name: yup.string().required('Plan name is required'),
    description: yup.string(),
    price: yup.number().required('Price is required').min(0, 'Price must be positive'),
    billing_period: yup.string().oneOf(['monthly', 'yearly'], 'Invalid billing period'),
    trial_period_days: yup.number().min(0, 'Trial period must be positive').nullable(),
    features: yup.array().of(yup.string()),
    is_active: yup.boolean(),
    sort_order: yup.number().integer('Sort order must be an integer'),
});

// Initialize form with preset data if available
const initialValues = props.preset 
    ? JSON.parse(props.preset)
    : {
        name: '',
        description: '',
        price: '',
        billing_period: 'monthly',
        trial_period_days: '',
        features: [],
        is_active: true,
        sort_order: 0,
    };

const { handleSubmit, errors, values } = useVeeForm({
    validationSchema,
    initialValues,
});

const newFeature = ref('');
const features = ref(
    initialValues.features.map((text: string, index: number) => ({
        id: crypto.randomUUID(),
        text,
        order: index,
    }))
);
const isDragging = ref(false);

const addFeature = () => {
    if (newFeature.value.trim()) {
        features.value.push({
            id: crypto.randomUUID(),
            text: newFeature.value.trim(),
            order: features.value.length,
        });
        updateFormFeatures();
        newFeature.value = '';
    }
};

const removeFeature = (id: string) => {
    features.value = features.value.filter(f => f.id !== id);
    updateFormFeatures();
};

const updateFormFeatures = () => {
    values.features = features.value
        .sort((a, b) => a.order - b.order)
        .map(f => f.text);
};

const onDragEnd = () => {
    isDragging.value = false;
    features.value = features.value.map((feature, index) => ({
        ...feature,
        order: index,
    }));
    updateFormFeatures();
};

const submit = handleSubmit(() => {
    router.post(route('admin.plans.store'), values, {
        onSuccess: () => {
            toast.success('Plan created successfully');
        },
        onError: () => {
            toast.error('Failed to create plan');
        },
    });
});
</script>

<template>
    <AppLayout title="Create Plan">
        <template #header>
            <h2 class="text-xl font-semibold text-gray-900">Create New Plan</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <div>
                            <InputLabel for="name" value="Plan Name" />
                            <TextInput
                                id="name"
                                v-model="values.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="errors.name" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                v-model="values.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <InputError :message="errors.description" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="price" value="Price" />
                                <TextInput
                                    id="price"
                                    v-model="values.price"
                                    type="number"
                                    step="0.01"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="errors.price" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="billing_period" value="Billing Period" />
                                <select
                                    id="billing_period"
                                    v-model="values.billing_period"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                                <InputError :message="errors.billing_period" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="trial_period_days" value="Trial Period (Days)" />
                                <TextInput
                                    id="trial_period_days"
                                    v-model="values.trial_period_days"
                                    type="number"
                                    step="1"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="errors.trial_period_days" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="sort_order" value="Sort Order" />
                                <TextInput
                                    id="sort_order"
                                    v-model="values.sort_order"
                                    type="number"
                                    step="1"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="errors.sort_order" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <InputLabel value="Features" />
                            <div class="mt-4 space-y-4">
                                <div class="flex gap-2">
                                    <TextInput
                                        v-model="newFeature"
                                        type="text"
                                        class="block w-full"
                                        placeholder="Add a feature..."
                                        @keyup.enter.prevent="addFeature"
                                    />
                                    <button
                                        type="button"
                                        @click="addFeature"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                    >
                                        Add
                                    </button>
                                </div>

                                <draggable
                                    v-model="features"
                                    :animation="150"
                                    ghost-class="bg-indigo-100"
                                    @start="isDragging = true"
                                    @end="onDragEnd"
                                    item-key="id"
                                    class="space-y-2"
                                >
                                    <template #item="{ element }">
                                        <li class="flex items-center justify-between p-2 bg-gray-50 rounded-md cursor-move">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                                </svg>
                                                <span>{{ element.text }}</span>
                                            </div>
                                            <button
                                                type="button"
                                                @click="removeFeature(element.id)"
                                                class="text-red-600 hover:text-red-800"
                                            >
                                                Remove
                                            </button>
                                        </li>
                                    </template>
                                </draggable>
                                <InputError :message="errors.features" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input
                                id="is_active"
                                v-model="values.is_active"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <InputLabel for="is_active" value="Active" class="ml-2" />
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                            >
                                Create Plan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 