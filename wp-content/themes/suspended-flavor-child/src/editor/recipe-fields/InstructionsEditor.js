/**
 * Instructions Editor Component
 *
 * Allows adding, editing, reordering, and grouping instruction steps.
 */

import { useState } from '@wordpress/element';
import {
    Button,
    TextControl,
    TextareaControl,
    Flex,
    FlexItem,
    FlexBlock,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const EMPTY_STEP = {
    text: '',
};

export default function InstructionsEditor({ instructions = [], onChange }) {
    const [dragIndex, setDragIndex] = useState(null);

    // Ensure we have at least one group
    const groups = instructions.length > 0 ? instructions : [{ title: '', steps: [] }];

    const updateGroups = (newGroups) => {
        onChange(newGroups);
    };

    const addGroup = () => {
        updateGroups([...groups, { title: '', steps: [] }]);
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

    const addStep = (groupIndex) => {
        const newGroups = [...groups];
        newGroups[groupIndex] = {
            ...newGroups[groupIndex],
            steps: [...newGroups[groupIndex].steps, { ...EMPTY_STEP }],
        };
        updateGroups(newGroups);
    };

    const removeStep = (groupIndex, stepIndex) => {
        const newGroups = [...groups];
        newGroups[groupIndex] = {
            ...newGroups[groupIndex],
            steps: newGroups[groupIndex].steps.filter((_, i) => i !== stepIndex),
        };
        updateGroups(newGroups);
    };

    const updateStep = (groupIndex, stepIndex, text) => {
        const newGroups = [...groups];
        const newSteps = [...newGroups[groupIndex].steps];
        newSteps[stepIndex] = { ...newSteps[stepIndex], text };
        newGroups[groupIndex] = { ...newGroups[groupIndex], steps: newSteps };
        updateGroups(newGroups);
    };

    const moveStep = (groupIndex, fromIndex, toIndex) => {
        if (fromIndex === toIndex) return;
        const newGroups = [...groups];
        const steps = [...newGroups[groupIndex].steps];
        const [moved] = steps.splice(fromIndex, 1);
        steps.splice(toIndex, 0, moved);
        newGroups[groupIndex] = { ...newGroups[groupIndex], steps };
        updateGroups(newGroups);
    };

    const handleDragStart = (e, groupIndex, stepIndex) => {
        setDragIndex({ group: groupIndex, step: stepIndex });
        e.dataTransfer.effectAllowed = 'move';
    };

    const handleDragOver = (e, groupIndex, stepIndex) => {
        e.preventDefault();
        if (dragIndex?.group !== groupIndex) return;
        if (dragIndex.step !== stepIndex) {
            moveStep(groupIndex, dragIndex.step, stepIndex);
            setDragIndex({ group: groupIndex, step: stepIndex });
        }
    };

    const handleDragEnd = () => {
        setDragIndex(null);
    };

    // Calculate step number across all groups
    const getStepNumber = (groupIndex, stepIndex) => {
        let count = 0;
        for (let g = 0; g < groupIndex; g++) {
            count += groups[g].steps.length;
        }
        return count + stepIndex + 1;
    };

    return (
        <div className="cjc-instructions-editor">
            {groups.map((group, groupIndex) => (
                <div key={groupIndex} className="cjc-instructions-group">
                    {groups.length > 1 && (
                        <Flex className="cjc-instructions-group-header">
                            <FlexBlock>
                                <TextControl
                                    placeholder={__('Section title (optional)', 'suspended-flavor-child')}
                                    value={group.title}
                                    onChange={(value) => updateGroupTitle(groupIndex, value)}
                                />
                            </FlexBlock>
                            <FlexItem>
                                <Button
                                    icon="trash"
                                    label={__('Remove section', 'suspended-flavor-child')}
                                    onClick={() => removeGroup(groupIndex)}
                                    isDestructive
                                />
                            </FlexItem>
                        </Flex>
                    )}

                    <div className="cjc-instructions-list">
                        {group.steps.map((step, stepIndex) => (
                            <div
                                key={stepIndex}
                                className="cjc-instruction-step"
                                draggable
                                onDragStart={(e) => handleDragStart(e, groupIndex, stepIndex)}
                                onDragOver={(e) => handleDragOver(e, groupIndex, stepIndex)}
                                onDragEnd={handleDragEnd}
                            >
                                <div className="cjc-instruction-step-header">
                                    <span className="cjc-instruction-drag-handle dashicons dashicons-menu"></span>
                                    <span className="cjc-instruction-step-number">
                                        {__('Step', 'suspended-flavor-child')} {getStepNumber(groupIndex, stepIndex)}
                                    </span>
                                    <Button
                                        icon="no-alt"
                                        label={__('Remove step', 'suspended-flavor-child')}
                                        onClick={() => removeStep(groupIndex, stepIndex)}
                                        isSmall
                                    />
                                </div>
                                <TextareaControl
                                    value={step.text}
                                    onChange={(value) => updateStep(groupIndex, stepIndex, value)}
                                    placeholder={__('Describe this step...', 'suspended-flavor-child')}
                                    rows={3}
                                />
                            </div>
                        ))}
                    </div>

                    <Button
                        icon="plus"
                        onClick={() => addStep(groupIndex)}
                        variant="secondary"
                        className="cjc-add-step-btn"
                    >
                        {__('Add Step', 'suspended-flavor-child')}
                    </Button>
                </div>
            ))}

            <Button
                icon="plus-alt2"
                onClick={addGroup}
                variant="tertiary"
                className="cjc-add-group-btn"
            >
                {__('Add Instruction Section', 'suspended-flavor-child')}
            </Button>
        </div>
    );
}
