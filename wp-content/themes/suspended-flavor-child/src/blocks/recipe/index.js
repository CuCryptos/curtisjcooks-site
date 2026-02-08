/**
 * CJC Recipe Block Registration
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';
import save from './save';

registerBlockType('cjc/recipe', {
    title: __('CJC Recipe', 'suspended-flavor-child'),
    description: __('Display an interactive recipe card.', 'suspended-flavor-child'),
    category: 'widgets',
    icon: 'food',
    keywords: [
        __('recipe', 'suspended-flavor-child'),
        __('cooking', 'suspended-flavor-child'),
        __('food', 'suspended-flavor-child'),
    ],
    supports: {
        html: false,
        align: ['wide', 'full'],
    },
    attributes: {
        recipeId: {
            type: 'number',
            default: 0,
        },
    },
    edit: Edit,
    save,
});
