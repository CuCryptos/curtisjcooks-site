/**
 * Nutrition Editor Component
 *
 * Sidebar panel for nutrition information fields.
 */

import { PanelBody, TextControl, Flex, FlexItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const NUTRITION_FIELDS = [
    { key: 'serving_size', label: __('Serving Size', 'suspended-flavor-child'), placeholder: 'e.g., 1 cup' },
    { key: 'calories', label: __('Calories', 'suspended-flavor-child'), placeholder: 'e.g., 250' },
    { key: 'fat', label: __('Total Fat', 'suspended-flavor-child'), placeholder: 'e.g., 12g' },
    { key: 'saturated_fat', label: __('Saturated Fat', 'suspended-flavor-child'), placeholder: 'e.g., 4g' },
    { key: 'unsaturated_fat', label: __('Unsaturated Fat', 'suspended-flavor-child'), placeholder: 'e.g., 8g' },
    { key: 'trans_fat', label: __('Trans Fat', 'suspended-flavor-child'), placeholder: 'e.g., 0g' },
    { key: 'cholesterol', label: __('Cholesterol', 'suspended-flavor-child'), placeholder: 'e.g., 45mg' },
    { key: 'sodium', label: __('Sodium', 'suspended-flavor-child'), placeholder: 'e.g., 480mg' },
    { key: 'carbohydrates', label: __('Total Carbohydrates', 'suspended-flavor-child'), placeholder: 'e.g., 30g' },
    { key: 'fiber', label: __('Dietary Fiber', 'suspended-flavor-child'), placeholder: 'e.g., 3g' },
    { key: 'sugar', label: __('Sugars', 'suspended-flavor-child'), placeholder: 'e.g., 8g' },
    { key: 'protein', label: __('Protein', 'suspended-flavor-child'), placeholder: 'e.g., 15g' },
];

export default function NutritionEditor({ nutrition = {}, onChange }) {
    return (
        <PanelBody title={__('Nutrition Information', 'suspended-flavor-child')} initialOpen={false}>
            <p className="components-base-control__help">
                {__('Enter nutritional values per serving. Include units (g, mg, etc.)', 'suspended-flavor-child')}
            </p>
            {NUTRITION_FIELDS.map((field) => (
                <TextControl
                    key={field.key}
                    label={field.label}
                    value={nutrition[field.key] || ''}
                    onChange={(value) => onChange(field.key, value)}
                    placeholder={field.placeholder}
                />
            ))}
        </PanelBody>
    );
}
