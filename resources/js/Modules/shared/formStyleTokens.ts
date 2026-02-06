export type FormAppearanceKey =
    | 'form-background'
    | 'form-background-text-color'
    | 'form-header-box'
    | 'form-header-box-text-color'
    | 'form-time-from'
    | 'form-time-from-text-color'
    | 'form-time-to'
    | 'form-time-to-text-color'
    | 'form-time-between'
    | 'form-time-between-text-color'
    | 'form-text-shadow';

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
        key: 'form-background-text-color',
        label: 'Card Background Text Color',
        description: 'Text color for the outer container that wraps the full guest card.',
    },
    {
        key: 'form-header-box',
        label: 'Header Banner',
        description: 'Title ribbon that displays the event name and ID.',
    },
    {
        key: 'form-header-box-text-color',
        label: 'Header Banner Text Color',
        description: 'Text color for the title ribbon that displays the event name and ID.',
    },
    {
        key: 'form-time-from',
        label: 'Start Time Pane',
        description: 'Left time panel that highlights when the event begins.',
    },
    {
        key: 'form-time-from-text-color',
        label: 'Start Time Pane Text Color',
        description: 'Text color for the left time panel that highlights when the event begins.',
    },
    {
        key: 'form-time-between',
        label: 'Time Divider',
        description: 'Small strip between the start and end time panels.',
    },
    {
        key: 'form-time-between-text-color',
        label: 'Time Divider Text Color',
        description: 'Text color for the small strip between the start and end time panels.',
    },
    {
        key: 'form-time-to',
        label: 'End Time Pane',
        description: 'Right time panel that shows when the event ends.',
    },
    {
        key: 'form-time-to-text-color',
        label: 'End Time Pane Text Color',
        description: 'Text color for the right time panel that shows when the event ends.',
    },
    {
        key: 'form-text-shadow',
        label: 'Text Shadow',
        description: 'Subtle shadow behind all text on the guest card to enhance readability.',
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
