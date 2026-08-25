import { describe, expect, it } from "vitest";
import { mount } from "@vue/test-utils";
import SelectSearchField from "./SelectSearchField.vue";

const TextInputStub = {
    props: ["modelValue"],
    emits: ["update:modelValue", "focusin", "click", "input", "clear"],
    template: `
        <input
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value); $emit('input', $event)"
        />
    `,
};

const TransitionContainerStub = {
    template: "<div><slot /></div>",
};

describe("SelectSearchField", () => {
    it("refreshes local options when the options prop changes", async () => {
        const wrapper = mount(SelectSearchField, {
            props: {
                id: "province",
                options: ["Nueva Ecija", "Bulacan"],
            },
            global: {
                stubs: {
                    "text-input": TextInputStub,
                    "search-icon": true,
                    "transition-container": TransitionContainerStub,
                },
            },
        });

        await wrapper.vm.$nextTick();

        expect(wrapper.vm.formattedOptions.map((option) => option.label)).toEqual(["Nueva Ecija", "Bulacan"]);

        await wrapper.setProps({
            options: ["Leyte", "Eastern Samar"],
        });

        expect(wrapper.vm.formattedOptions.map((option) => option.label)).toEqual(["Leyte", "Eastern Samar"]);
        expect(wrapper.vm.filteredOptions.map((option) => option.label)).toEqual(["Leyte", "Eastern Samar"]);
    });
});
