export function formatDate(
    value?: string | number | Date | null,
    isDateOnly: boolean = true,
    locale: string = 'de-AT',
): string {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    const date = value instanceof Date ? value : new Date(value);

    // If the date is invalid, return '-'
    if (Number.isNaN(date.getTime())) {
        return '-';
    }

    return isDateOnly ? date.toLocaleDateString(locale) : date.toLocaleString(locale);
}
