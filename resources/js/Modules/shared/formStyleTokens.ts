export type FormAppearanceKey =
    | 'form-background'
    | 'form-header-box'
    | 'form-time-from'
    | 'form-time-to'
    | 'form-time-between';

export type FormAppearanceMode = 'color' | 'image' | null;

export interface FormAppearanceToken {
    mode: FormAppearanceMode;
    value: string | null;
}

export type FormAppearanceTokens = Record<FormAppearanceKey, FormAppearanceToken>;

export interface FormStyleFieldDefinition {
    key: FormAppearanceKey;
    label: string;
    description: string;
}

export const FORM_STYLE_FIELDS: ReadonlyArray<FormStyleFieldDefinition> = [
    {
        key: 'form-background',
        label: 'Card Background',
        description: 'Outer container that wraps the full guest card.',
    },
    {
        key: 'form-header-box',
        label: 'Header Banner',
        description: 'Title ribbon that displays the event name and ID.',
    },
    {
        key: 'form-time-from',
        label: 'Start Time Pane',
        description: 'Left time panel that highlights when the event begins.',
    },
    {
        key: 'form-time-between',
        label: 'Time Divider',
        description: 'Small strip between the start and end time panels.',
    },
    {
        key: 'form-time-to',
        label: 'End Time Pane',
        description: 'Right time panel that shows when the event ends.',
    },
];

export const createEmptyFormStyleTokens = (): FormAppearanceTokens => {
    return FORM_STYLE_FIELDS.reduce((tokens, field) => {
        tokens[field.key] = { mode: null, value: null } as FormAppearanceToken;
        return tokens;
    }, {} as FormAppearanceTokens);
};

export const mergeFormStyleTokens = (
    incoming?: Partial<Record<FormAppearanceKey, Partial<FormAppearanceToken>>> | null
): FormAppearanceTokens => {
    const template = createEmptyFormStyleTokens();

    if (!incoming) {
        return template;
    }

    (Object.keys(template) as FormAppearanceKey[]).forEach((key) => {
        template[key] = {
            mode: incoming?.[key]?.mode ?? null,
            value: incoming?.[key]?.value ?? null,
        };
    });

    return template;
};

export const styleTokensHaveCustomValues = (
    tokens?: Partial<Record<FormAppearanceKey, Partial<FormAppearanceToken>>> | null
): boolean => {
    if (!tokens) {
        return false;
    }

    return (Object.values(tokens) as Array<Partial<FormAppearanceToken> | undefined>).some(
        (token) => !!token?.value
    );
};
