<template>
    <section class="block details">
        <EditableField field="product.description" label="পণ্যের বিবরণ" :editable="landing.editable" @edit="landing.edit">
            <h2>{{ landing.content.headings?.details || 'পণ্যের বিবরণ' }}</h2>
            <p class="details__lead">{{ landing.content.product?.description }}</p>
        </EditableField>

        <div v-if="specs.length" class="details__specs">
            <h3>বিবরণের সারি</h3>
            <EditableField field="details.specs" label="স্পেসিফিকেশন" :editable="landing.editable" as="div" @edit="landing.edit">
                <dl>
                    <template v-for="(row, index) in specs" :key="index">
                        <dt>{{ row.label }}</dt>
                        <dd>{{ row.value }}</dd>
                    </template>
                </dl>
            </EditableField>
        </div>

        <div v-if="ingredients.length" class="details__ingredients">
            <h3>উপাদান</h3>
            <EditableField field="details.ingredients" label="উপাদান" :editable="landing.editable" as="ul" @edit="landing.edit">
                <li v-for="(item, index) in ingredients" :key="index">
                    <strong>{{ item.name }}</strong>
                    <span>{{ item.note }}</span>
                </li>
            </EditableField>
        </div>

        <div v-if="materials" class="details__materials">
            <h3>উপকরণ</h3>
            <EditableField field="details.materials" label="উপকরণ" :editable="landing.editable" @edit="landing.edit">
                <p>{{ materials }}</p>
            </EditableField>
        </div>

        <div v-if="included.length">
            <h3>যা থাকছে</h3>
            <EditableField field="details.included" label="যা থাকছে" :editable="landing.editable" as="ul" @edit="landing.edit">
                <li v-for="(item, index) in included" :key="index">{{ item.text }}</li>
            </EditableField>
        </div>

        <div v-if="usage.length" class="details__steps">
            <h3>{{ usageTitle }}</h3>
            <EditableField field="details.usage" label="ব্যবহার" :editable="landing.editable" as="ol" @edit="landing.edit">
                <li v-for="(step, index) in usage" :key="index"><span>{{ index + 1 }}</span>{{ step.text }}</li>
            </EditableField>
        </div>
    </section>
</template>

<script setup>
import { computed, inject } from 'vue';
import EditableField from '../builder/EditableField.vue';
import { landingKey } from '../../landing/helpers';

const landing = inject(landingKey);
const details = computed(() => landing.content.details || {});
const specs = computed(() => (details.value.specs || []).filter((row) => row?.label && row?.value));
const ingredients = computed(() => (details.value.ingredients || []).filter((row) => row?.name));
const included = computed(() => (details.value.included || []).filter((row) => row?.text));
const usage = computed(() => (details.value.usage || []).filter((row) => row?.text));
const materials = computed(() => String(details.value.materials || '').trim());
const usageTitle = computed(() => landing.design === 'smart' ? 'কীভাবে ব্যবহার করবেন' : (landing.design === 'abhijatya' ? 'যত্নের নিয়ম' : 'ব্যবহার'));
</script>
