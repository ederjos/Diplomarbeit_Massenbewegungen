import { mount } from '@vue/test-utils';
import { test, expect } from 'vitest';
import PasswordInput from './PasswordInput.vue';

test('default input type is password', () => {
    const wrapper = mount(PasswordInput, {
        props: {modelValue: 'secret' },
    });
    const input = wrapper.find('input');
    expect(input.attributes('type')).toBe('password');
});


test('toggles to text input when eye button is clicked', async () => {
    const wrapper = mount(PasswordInput, {
        props: { modelValue: 'secret' },
    });
    await wrapper.find('button').trigger('click');

    const input = wrapper.find('input');
    expect(input.attributes('type')).toBe('text');
});

test('toggles back to password on second click', async () => {
    const wrapper = mount(PasswordInput, {
        props: { modelValue: 'secret' },
    });
    await wrapper.find('button').trigger('click');
    await wrapper.find('button').trigger('click');
    expect(wrapper.find('input').attributes('type')).toBe('password');
});

test('emits update:modelValue when typing', async () => {
    const wrapper = mount(PasswordInput, {
        props: { modelValue: 'secret' },
    });
    await wrapper.find('input').setValue('secret123');
    expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['secret123']);
});
