/**
 * Times Editor Component
 *
 * Inputs for prep time, cook time, total time, and yield.
 */

import {
    TextControl,
    Flex,
    FlexItem,
    __experimentalNumberControl as NumberControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function TimesEditor({
    prepTime,
    cookTime,
    totalTime,
    recipeYield,
    yieldNumber,
    onChange,
}) {
    return (
        <div className="cjc-times-editor">
            <Flex gap={4} wrap>
                <FlexItem>
                    <TextControl
                        label={__('Prep Time', 'suspended-flavor-child')}
                        value={prepTime}
                        onChange={(value) => onChange('prep_time', value)}
                        placeholder={__('e.g., 15 minutes', 'suspended-flavor-child')}
                        className="cjc-time-input"
                    />
                </FlexItem>
                <FlexItem>
                    <TextControl
                        label={__('Cook Time', 'suspended-flavor-child')}
                        value={cookTime}
                        onChange={(value) => onChange('cook_time', value)}
                        placeholder={__('e.g., 30 minutes', 'suspended-flavor-child')}
                        className="cjc-time-input"
                    />
                </FlexItem>
                <FlexItem>
                    <TextControl
                        label={__('Total Time', 'suspended-flavor-child')}
                        value={totalTime}
                        onChange={(value) => onChange('total_time', value)}
                        placeholder={__('e.g., 45 minutes', 'suspended-flavor-child')}
                        help={__('Leave blank to auto-calculate', 'suspended-flavor-child')}
                        className="cjc-time-input"
                    />
                </FlexItem>
            </Flex>
            <Flex gap={4} wrap style={{ marginTop: '16px' }}>
                <FlexItem>
                    <TextControl
                        label={__('Yield', 'suspended-flavor-child')}
                        value={recipeYield}
                        onChange={(value) => onChange('yield', value)}
                        placeholder={__('e.g., 4 servings', 'suspended-flavor-child')}
                        className="cjc-yield-input"
                    />
                </FlexItem>
                <FlexItem>
                    {NumberControl ? (
                        <NumberControl
                            label={__('Servings Number', 'suspended-flavor-child')}
                            value={yieldNumber}
                            onChange={(value) => onChange('yield_number', parseInt(value, 10) || 0)}
                            min={1}
                            max={100}
                            help={__('Used for scaling', 'suspended-flavor-child')}
                            className="cjc-yield-number-input"
                        />
                    ) : (
                        <TextControl
                            label={__('Servings Number', 'suspended-flavor-child')}
                            value={yieldNumber}
                            onChange={(value) => onChange('yield_number', parseInt(value, 10) || 0)}
                            type="number"
                            min={1}
                            max={100}
                            help={__('Used for scaling', 'suspended-flavor-child')}
                            className="cjc-yield-number-input"
                        />
                    )}
                </FlexItem>
            </Flex>
        </div>
    );
}
