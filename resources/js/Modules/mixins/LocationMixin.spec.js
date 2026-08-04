import { describe, expect, it, vi } from "vitest";
import { mount } from "@vue/test-utils";
import LocationMixin from "./LocationMixin.ts";

const TestLocationComponent = {
    template: "<div />",
    mixins: [LocationMixin],
    methods: {
        fetchGetApi(...args) {
            return this.fetchGetApiMock(...args);
        },
    },
    data() {
        return {
            fetchGetApiMock: vi.fn(),
        };
    },
};

describe("LocationMixin", () => {
    it("keeps the latest province response when requests resolve out of order", async () => {
        const pendingResponses = new Map();
        const wrapper = mount(TestLocationComponent);

        wrapper.vm.fetchGetApiMock = vi.fn((route, params = {}) => {
            return new Promise((resolve) => {
                pendingResponses.set(params.region, resolve);
            });
        });

        const firstRequest = wrapper.vm.loadProvinces("Region III");
        const secondRequest = wrapper.vm.loadProvinces("Region VIII");

        pendingResponses.get("Region VIII")({ data: ["Leyte"] });
        await secondRequest;

        pendingResponses.get("Region III")({ data: ["Nueva Ecija"] });
        await firstRequest;

        expect(wrapper.vm.locationProvinces).toEqual(["Leyte"]);
    });

    it("ignores a pending province response after the province list is reset", async () => {
        let resolveRequest;
        const wrapper = mount(TestLocationComponent);

        wrapper.vm.fetchGetApiMock = vi.fn(() => {
            return new Promise((resolve) => {
                resolveRequest = resolve;
            });
        });

        const pendingRequest = wrapper.vm.loadProvinces("Region III");
        wrapper.vm.resetLocationProvinces();

        resolveRequest({ data: ["Nueva Ecija"] });
        await pendingRequest;

        expect(wrapper.vm.locationProvinces).toEqual([]);
    });
});
