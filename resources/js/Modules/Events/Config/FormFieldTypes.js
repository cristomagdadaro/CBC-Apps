/**
 * Form Field Types Configuration
 * 
 * Defines all available field types for the dynamic form builder.
 * Each type specifies its component, validation capabilities, and UI config.
 */

export const FIELD_TYPES = {
    // Layout Types 
    section_header: {
        label: 'Section Header',
        icon: 'bars-3',
        component: 'FormFieldSectionHeader',
        category: 'layout',
        supportedValidations: [],
        defaultConfig: {},
        isDecorative: true,
    },
    
    paragraph: {
        label: 'Paragraph',
        icon: 'document-text',
        component: 'FormFieldParagraph',
        category: 'layout',
        supportedValidations: [],
        defaultConfig: {},
        isDecorative: true,
    },
    // Basic Input Types
    text: {
        label: 'Short Text',
        icon: 'text',
        component: 'FormFieldText',
        category: 'basic',
        supportedValidations: ['required', 'min', 'max', 'regex'],
        defaultConfig: { maxLength: 255 },
    },
    
    textarea: {
        label: 'Long Text',
        icon: 'document-text',
        component: 'FormFieldTextarea',
        category: 'basic',
        supportedValidations: ['required', 'min', 'max'],
        defaultConfig: { rows: 4, maxLength: 2000 },
    },
    
    number: {
        label: 'Number',
        icon: 'hashtag',
        component: 'FormFieldNumber',
        category: 'basic',
        supportedValidations: ['required', 'min', 'max', 'integer'],
        defaultConfig: { step: 1 },
    },
    
    email: {
        label: 'Email',
        icon: 'envelope',
        component: 'FormFieldEmail',
        category: 'basic',
        supportedValidations: ['required'],
        defaultConfig: {},
    },
    
    phone: {
        label: 'Phone',
        icon: 'phone',
        component: 'FormFieldPhone',
        category: 'basic',
        supportedValidations: ['required'],
        defaultConfig: { format: 'PH' },
    },
    
    date: {
        label: 'Date',
        icon: 'calendar',
        component: 'FormFieldDate',
        category: 'basic',
        supportedValidations: ['required', 'after', 'before'],
        defaultConfig: { format: 'Y-m-d' },
    },
    
    time: {
        label: 'Time',
        icon: 'clock',
        component: 'FormFieldTime',
        category: 'basic',
        supportedValidations: ['required'],
        defaultConfig: { format: '12h' },
    },
    
    datetime: {
        label: 'Date & Time',
        icon: 'calendar-days',
        component: 'FormFieldDateTime',
        category: 'basic',
        supportedValidations: ['required', 'after', 'before'],
        defaultConfig: {},
    },

    // Choice Types
    select: {
        label: 'Dropdown',
        icon: 'chevron-down',
        component: 'FormFieldSelect',
        category: 'choice',
        supportedValidations: ['required'],
        defaultConfig: { placeholder: 'Choose an option' },
        hasOptions: true,
        supportsSkipLogic: true,
    },
    
    radio: {
        label: 'Radio Buttons',
        icon: 'radio-button',
        component: 'FormFieldRadio',
        category: 'choice',
        supportedValidations: ['required'],
        defaultConfig: { layout: 'vertical' },
        hasOptions: true,
        supportsSkipLogic: true,
    },
    
    checkbox: {
        label: 'Checkbox',
        icon: 'check-square',
        component: 'FormFieldCheckbox',
        category: 'choice',
        supportedValidations: ['required'],
        defaultConfig: {},
    },
    
    checkbox_group: {
        label: 'Checkbox Group',
        icon: 'list-bullet',
        component: 'FormFieldCheckboxGroup',
        category: 'choice',
        supportedValidations: ['required', 'min', 'max'],
        defaultConfig: { layout: 'vertical' },
        hasOptions: true,
    },
    
    checkbox_agreement: {
        label: 'Agreement Checkbox',
        icon: 'shield-check',
        component: 'FormFieldAgreement',
        category: 'choice',
        supportedValidations: ['required'],
        defaultConfig: { agreementText: 'I agree to the terms and conditions' },
    },

    // Scale Types
    likert_scale: {
        label: 'Likert Scale',
        icon: 'star',
        component: 'FormFieldLikertScale',
        category: 'scale',
        supportedValidations: ['required'],
        defaultConfig: {
            min: 1,
            max: 5,
            labels: { 1: 'Poor', 5: 'Excellent' },
        },
    },
    
    linear_scale: {
        label: 'Linear Scale',
        icon: 'chart-bar',
        component: 'FormFieldLinearScale',
        category: 'scale',
        supportedValidations: ['required'],
        defaultConfig: {
            min: 1,
            max: 10,
            minLabel: '',
            maxLabel: '',
        },
    },

    // Grid Types
    checkbox_grid: {
        label: 'Checkbox Grid',
        icon: 'table-cells',
        component: 'FormFieldCheckboxGrid',
        category: 'grid',
        supportedValidations: ['required'],
        defaultConfig: {},
        hasRows: true,
        hasColumns: true,
    },
    
    radio_grid: {
        label: 'Radio Grid',
        icon: 'table-cells',
        component: 'FormFieldRadioGrid',
        category: 'grid',
        supportedValidations: ['required'],
        defaultConfig: {},
        hasRows: true,
        hasColumns: true,
    },

    // Special Types
    file: {
        label: 'File Upload',
        icon: 'paper-clip',
        component: 'FormFieldFile',
        category: 'special',
        supportedValidations: ['required', 'mimes', 'max'],
        defaultConfig: {
            accept: '.pdf,.doc,.docx',
            maxSize: 2048,
        },
    },

    // Location Types
    location_city: {
        label: 'City (PH)',
        icon: 'map-pin',
        component: 'FormFieldLocationCity',
        category: 'location',
        supportedValidations: ['required'],
        defaultConfig: {},
    },
    
    location_province: {
        label: 'Province (PH)',
        icon: 'map',
        component: 'FormFieldLocationProvince',
        category: 'location',
        supportedValidations: ['required'],
        defaultConfig: {},
    },
    
    location_region: {
        label: 'Region (PH)',
        icon: 'globe-asia-australia',
        component: 'FormFieldLocationRegion',
        category: 'location',
        supportedValidations: ['required'],
        defaultConfig: {},
    },
};

export const FIELD_CATEGORIES = {
    layout: { label: 'Layout Elements', icon: 'squares-2x2' },
    basic: { label: 'Basic Fields', icon: 'pencil-square' },
    choice: { label: 'Choice Fields', icon: 'list-bullet' },
    scale: { label: 'Scale Fields', icon: 'star' },
    grid: { label: 'Grid Fields', icon: 'table-cells' },
    location: { label: 'Location Fields', icon: 'map' },
    special: { label: 'Special Fields', icon: 'sparkles' },
};

export const VALIDATION_TYPES = {
    required: { label: 'Required', icon: 'asterisk', hasValue: false },
    min: { label: 'Minimum', icon: 'arrow-down', hasValue: true, valueType: 'number' },
    max: { label: 'Maximum', icon: 'arrow-up', hasValue: true, valueType: 'number' },
    regex: { label: 'Pattern', icon: 'code', hasValue: true, valueType: 'text' },
    integer: { label: 'Integer Only', icon: 'hashtag', hasValue: false },
    mimes: { label: 'File Types', icon: 'document', hasValue: true, valueType: 'text' },
    after: { label: 'After Date', icon: 'calendar', hasValue: true, valueType: 'date' },
    before: { label: 'Before Date', icon: 'calendar', hasValue: true, valueType: 'date' },
};

/**
 * Get fields grouped by category
 */
export function getFieldsByCategory() {
    const grouped = {};
    for (const [key, config] of Object.entries(FIELD_TYPES)) {
        const category = config.category;
        if (!grouped[category]) {
            grouped[category] = [];
        }
        grouped[category].push({ key, ...config });
    }
    return grouped;
}

/**
 * Get field config by type key
 */
export function getFieldConfig(fieldType) {
    return FIELD_TYPES[fieldType] || null;
}

/**
 * Check if field type supports a validation
 */
export function supportsValidation(fieldType, validationType) {
    const config = FIELD_TYPES[fieldType];
    return config?.supportedValidations?.includes(validationType) || false;
}

export default {
    FIELD_TYPES,
    FIELD_CATEGORIES,
    VALIDATION_TYPES,
    getFieldsByCategory,
    getFieldConfig,
    supportsValidation,
};
