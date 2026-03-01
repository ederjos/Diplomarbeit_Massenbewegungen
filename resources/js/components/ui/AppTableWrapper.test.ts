import { mount } from '@vue/test-utils';
import { test, expect, vi } from 'vitest';
import AppTableWrapper from './AppTableWrapper.vue';

// SortableHeader emits 'sort' internally, AppTableWrapper translates that to 'sort-by'
vi.mock('@/components/ui/SortableHeader.vue', () => ({
    default: {
        template: '<th @click="$emit(\'sort\')"><slot />{{ label }}</th>',
        props: ['label', 'isActive', 'direction', 'styleClass'],
        emits: ['sort'],
    },
}));

// Also mock the columns prop type for better type checking
// AppTableWrapper is generic and doesn't know the actual column names
type Row = {
    name: string;
    value: string;
    action: string;
};

type Column = {
    label: string;
    columnName: keyof Row | null;
};

const columns: Column[] = [
    { label: 'Name', columnName: 'name' },
    { label: 'Value', columnName: 'value' },
    { label: 'Actions', columnName: 'action' },
];

test('renders all column headers', () => {
    const wrapper = mount(AppTableWrapper<Row>, {
        props: {
            columns,
            sortColumn: null,
            sortDirection: 'asc',
        },
    });
    expect(wrapper.text()).toContain('Name');
    expect(wrapper.text()).toContain('Value');
    expect(wrapper.text()).toContain('Actions');
});

test('renders slot content in tbody', () => {
    const wrapper = mount(AppTableWrapper<Row>, {
        props: {
            columns,
            sortColumn: null,
            sortDirection: 'asc',
        },
        slots: {
            default: '<tr><td>Test row</td></tr>',
        },
    });
    expect(wrapper.find('tbody').text()).toContain('Test row');
});

test('emits "sort-by" when a sortable header is clicked', async () => {
    const wrapper = mount(AppTableWrapper<Row>, {
        props: {
            columns,
            sortColumn: null,
            sortDirection: 'asc',
        },
    });
    const headers = wrapper.findAll('th');
    await headers[0].trigger('click');
    expect(wrapper.emitted('sort-by')?.[0]).toEqual(['name']);
});
