/**
 * Ingredients Editor Component
 *
 * Allows adding, editing, reordering, and grouping ingredients.
 */

import { useState } from '@wordpress/element';
import {
    Button,
    TextControl,
    Flex,
    FlexItem,
    FlexBlock,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const EMPTY_INGREDIENT = {
    amount: '',
    unit: '',
    name: '',
    notes: '',
};

const COMMON_UNITS = [
    '', 'cup', 'cups', 'tbsp', 'tsp', 'oz', 'lb', 'g', 'kg', 'ml', 'l',
    'slice', 'slices', 'piece', 'pieces', 'clove', 'cloves', 'pinch',
    'dash', 'handful', 'bunch', 'can', 'package', 'small', 'medium', 'large'
];

export default function IngredientsEditor({ ingredients = [], onChange }) {
    const [dragIndex, setDragIndex] = useState(null);
    const [dragType, setDragType] = useState(null); // 'group' or 'item'

    // Ensure we have at least one group
    const groups = ingredients.length > 0 ? ingredients : [{ title: '', items: [] }];

    const updateGroups = (newGroups) => {
        onChange(newGroups);
    };

    const addGroup = () => {
        updateGroups([...groups, { title: '', items: [] }]);
    };

    const removeGroup = (groupIndex) => {
        if (groups.length <= 1) return;
        const newGroups = groups.filter((_, i) => i !== groupIndex);
        updateGroups(newGroups);
    };

    const updateGroupTitle = (groupIndex, title) => {
        const newGroups = [...groups];
        newGroups[groupIndex] = { ...newGroups[groupIndex], title };
        updateGroups(newGroups);
    };

    const addIngredient = (groupIndex) => {
        const newGroups = [...groups];
        newGroups[groupIndex] = {
            ...newGroups[groupIndex],
            items: [...newGroups[groupIndex].items, { ...EMPTY_INGREDIENT }],
        };
        updateGroups(newGroups);
    };

    const removeIngredient = (groupIndex, itemIndex) => {
        const newGroups = [...groups];
        newGroups[groupIndex] = {
            ...newGroups[groupIndex],
            items: newGroups[groupIndex].items.filter((_, i) => i !== itemIndex),
        };
        updateGroups(newGroups);
    };

    const updateIngredient = (groupIndex, itemIndex, field, value) => {
        const newGroups = [...groups];
        const newItems = [...newGroups[groupIndex].items];
        newItems[itemIndex] = { ...newItems[itemIndex], [field]: value };
        newGroups[groupIndex] = { ...newGroups[groupIndex], items: newItems };
        updateGroups(newGroups);
    };

    const moveIngredient = (groupIndex, fromIndex, toIndex) => {
        if (fromIndex === toIndex) return;
        const newGroups = [...groups];
        const items = [...newGroups[groupIndex].items];
        const [moved] = items.splice(fromIndex, 1);
        items.splice(toIndex, 0, moved);
        newGroups[groupIndex] = { ...newGroups[groupIndex], items };
        updateGroups(newGroups);
    };

    const handleDragStart = (e, groupIndex, itemIndex) => {
        setDragIndex({ group: groupIndex, item: itemIndex });
        setDragType('item');
        e.dataTransfer.effectAllowed = 'move';
    };

    const handleDragOver = (e, groupIndex, itemIndex) => {
        e.preventDefault();
        if (dragType !== 'item' || dragIndex.group !== groupIndex) return;
        if (dragIndex.item !== itemIndex) {
            moveIngredient(groupIndex, dragIndex.item, itemIndex);
            setDragIndex({ group: groupIndex, item: itemIndex });
        }
    };

    const handleDragEnd = () => {
        setDragIndex(null);
        setDragType(null);
    };

    return (
        <div className="cjc-ingredients-editor">
            {groups.map((group, groupIndex) => (
                <div key={groupIndex} className="cjc-ingredients-group">
                    {groups.length > 1 && (
                        <Flex className="cjc-ingredients-group-header">
                            <FlexBlock>
                                <TextControl
                                    placeholder={__('Group title (optional)', 'suspended-flavor-child')}
                                    value={group.title}
                                    onChange={(value) => updateGroupTitle(groupIndex, value)}
                                />
                            </FlexBlock>
                            <FlexItem>
                                <Button
                                    icon="trash"
                                    label={__('Remove group', 'suspended-flavor-child')}
                                    onClick={() => removeGroup(groupIndex)}
                                    isDestructive
                                />
                            </FlexItem>
                        </Flex>
                    )}

                    <div className="cjc-ingredients-list">
                        {group.items.map((item, itemIndex) => (
                            <div
                                key={itemIndex}
                                className="cjc-ingredient-item"
                                draggable
                                onDragStart={(e) => handleDragStart(e, groupIndex, itemIndex)}
                                onDragOver={(e) => handleDragOver(e, groupIndex, itemIndex)}
                                onDragEnd={handleDragEnd}
                            >
                                <span className="cjc-ingredient-drag-handle dashicons dashicons-menu"></span>
                                <Flex gap={2} wrap>
                                    <FlexItem>
                                        <TextControl
                                            placeholder={__('Amt', 'suspended-flavor-child')}
                                            value={item.amount}
                                            onChange={(value) =>
                                                updateIngredient(groupIndex, itemIndex, 'amount', value)
                                            }
                                            className="cjc-ingredient-amount"
                                        />
                                    </FlexItem>
                                    <FlexItem>
                                        <select
                                            value={item.unit}
                                            onChange={(e) =>
                                                updateIngredient(groupIndex, itemIndex, 'unit', e.target.value)
                                            }
                                            className="cjc-ingredient-unit components-select-control__input"
                                        >
                                            {COMMON_UNITS.map((unit) => (
                                                <option key={unit} value={unit}>
                                                    {unit || __('unit', 'suspended-flavor-child')}
                                                </option>
                                            ))}
                                        </select>
                                    </FlexItem>
                                    <FlexBlock>
                                        <TextControl
                                            placeholder={__('Ingredient name', 'suspended-flavor-child')}
                                            value={item.name}
                                            onChange={(value) =>
                                                updateIngredient(groupIndex, itemIndex, 'name', value)
                                            }
                                            className="cjc-ingredient-name"
                                        />
                                    </FlexBlock>
                                    <FlexItem>
                                        <TextControl
                                            placeholder={__('Notes', 'suspended-flavor-child')}
                                            value={item.notes}
                                            onChange={(value) =>
                                                updateIngredient(groupIndex, itemIndex, 'notes', value)
                                            }
                                            className="cjc-ingredient-notes"
                                        />
                                    </FlexItem>
                                    <FlexItem>
                                        <Button
                                            icon="no-alt"
                                            label={__('Remove', 'suspended-flavor-child')}
                                            onClick={() => removeIngredient(groupIndex, itemIndex)}
                                            isSmall
                                        />
                                    </FlexItem>
                                </Flex>
                            </div>
                        ))}
                    </div>

                    <Button
                        icon="plus"
                        onClick={() => addIngredient(groupIndex)}
                        variant="secondary"
                        className="cjc-add-ingredient-btn"
                    >
                        {__('Add Ingredient', 'suspended-flavor-child')}
                    </Button>
                </div>
            ))}

            <Button
                icon="plus-alt2"
                onClick={addGroup}
                variant="tertiary"
                className="cjc-add-group-btn"
            >
                {__('Add Ingredient Group', 'suspended-flavor-child')}
            </Button>
        </div>
    );
}
