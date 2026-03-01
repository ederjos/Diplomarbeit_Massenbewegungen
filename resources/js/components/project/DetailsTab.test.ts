import { mount } from '@vue/test-utils';
import { test, expect } from 'vitest';
import type { ProjectDetails } from '@/@types/project';
import type { User } from '@/@types/user';
import DetailsTab from './DetailsTab.vue';

const project: ProjectDetails = {
    id: 12345,
    name: 'Project 1',
    isActive: true,
    comment: 'This is a test project.',
    lastFileNumber: 42,
    period: '2 mons',
    client: 'Test Client',
    clerk: 'Test Clerk',
    municipality: 'Bregenz',
    type: 'Test Type',
    movementMagnitude: '5cm/per year over the last two years',
    firstMeasurement: '2025-01-01',
    lastMeasurement: '2026-01-01',
};

const contactPersons: User[] = [
    { id: 1, name: 'Alice', role: { id: 1, name: 'Admin' } },
    { id: 2, name: 'Bob', role: { id: 1, name: 'Guest' } },
];

test('key project fields are rendered', () => {
    const wrapper = mount(DetailsTab, {
        props: {
            project,
            contactPersons,
        },
    });
    // ID
    expect(wrapper.text()).toContain('12345');
    // Name
    expect(wrapper.text()).toContain('Project 1');
    // Comment
    expect(wrapper.text()).toContain('This is a test project.');
    // Last file number
    expect(wrapper.text()).toContain('42');
    // Period
    expect(wrapper.text()).toContain('2 mons');
    // Client
    expect(wrapper.text()).toContain('Test Client');
    // Clerk
    expect(wrapper.text()).toContain('Test Clerk');
    // Municipality
    expect(wrapper.text()).toContain('Bregenz');
    // Type
    expect(wrapper.text()).toContain('Test Type');
    // Movement magnitude
    expect(wrapper.text()).toContain('5cm/per year over the last two years');
});

test('active projects render "Aktiv"', () => {
    const wrapper = mount(DetailsTab, {
        props: {
            project,
            contactPersons,
        },
    });
    expect(wrapper.text()).toContain('Aktiv');
    expect(wrapper.text()).not.toContain('Inaktiv');
});

test('inactive projects render "Inaktiv"', () => {
    const wrapper = mount(DetailsTab, {
        props: {
            project: { ...project, isActive: false },
            contactPersons,
        },
    });
    expect(wrapper.text()).toContain('Inaktiv');
    expect(wrapper.text()).not.toContain('Aktiv');
});

test('contact persons are rendered with roles', () => {
    const wrapper = mount(DetailsTab, {
        props: {
            project,
            contactPersons,
        },
    });
    expect(wrapper.text()).toContain('Alice (Admin)');
    expect(wrapper.text()).toContain('Bob (Guest)');
});

test('singular label for one contact person used', () => {
    const wrapper = mount(DetailsTab, {
        props: {
            project,
            contactPersons: [contactPersons[0]],
        },
    });
    expect(wrapper.text()).toContain('Ansprechperson');
    expect(wrapper.text()).not.toContain('Ansprechpersonen');
});

test('plural label used for multiple contact persons', () => {
    const wrapper = mount(DetailsTab, {
        props: {
            project,
            contactPersons,
        },
    });
    expect(wrapper.text()).toContain('Ansprechpersonen');
});
