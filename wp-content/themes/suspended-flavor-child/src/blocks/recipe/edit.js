/**
 * CJC Recipe Block Editor
 */

import { useState, useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import {
    BlockControls,
    InspectorControls,
    MediaUpload,
    MediaUploadCheck,
} from '@wordpress/block-editor';
import {
    Button,
    Panel,
    PanelBody,
    PanelRow,
    TextControl,
    TextareaControl,
    SelectControl,
    Placeholder,
    Spinner,
    ToolbarGroup,
    ToolbarButton,
    Modal,
    Notice,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

import IngredientsEditor from '../../editor/recipe-fields/IngredientsEditor';
import InstructionsEditor from '../../editor/recipe-fields/InstructionsEditor';
import TimesEditor from '../../editor/recipe-fields/TimesEditor';
import NutritionEditor from '../../editor/recipe-fields/NutritionEditor';

const DEFAULT_RECIPE_DATA = {
    title: '',
    description: '',
    prep_time: '',
    cook_time: '',
    total_time: '',
    yield: '',
    yield_number: 4,
    category: '',
    cuisine: '',
    method: '',
    diet: '',
    keywords: '',
    ingredients: [{ title: '', items: [] }],
    instructions: [{ title: '', steps: [] }],
    notes: '',
    video_url: '',
    image_id: 0,
    image_url: '',
    nutrition: {
        serving_size: '',
        calories: '',
        sugar: '',
        sodium: '',
        fat: '',
        saturated_fat: '',
        unsaturated_fat: '',
        trans_fat: '',
        carbohydrates: '',
        fiber: '',
        protein: '',
        cholesterol: '',
    },
};

export default function Edit({ attributes, setAttributes, clientId }) {
    const { recipeId } = attributes;
    const [recipeData, setRecipeData] = useState(DEFAULT_RECIPE_DATA);
    const [isLoading, setIsLoading] = useState(false);
    const [isSaving, setIsSaving] = useState(false);
    const [hasChanges, setHasChanges] = useState(false);
    const [showRecipeSelector, setShowRecipeSelector] = useState(false);
    const [availableRecipes, setAvailableRecipes] = useState([]);
    const [error, setError] = useState(null);

    const { createSuccessNotice, createErrorNotice } = useDispatch('core/notices');

    // Load recipe data when recipeId changes
    useEffect(() => {
        if (recipeId) {
            loadRecipeData(recipeId);
        }
    }, [recipeId]);

    // Load available recipes for selector
    useEffect(() => {
        if (showRecipeSelector) {
            loadAvailableRecipes();
        }
    }, [showRecipeSelector]);

    const loadRecipeData = async (id) => {
        setIsLoading(true);
        setError(null);
        try {
            const data = await apiFetch({
                path: `/cjc/v1/recipes/${id}`,
            });
            setRecipeData({ ...DEFAULT_RECIPE_DATA, ...data });
            setHasChanges(false);
        } catch (err) {
            setError(__('Failed to load recipe data.', 'suspended-flavor-child'));
            console.error('Error loading recipe:', err);
        }
        setIsLoading(false);
    };

    const loadAvailableRecipes = async () => {
        try {
            const recipes = await apiFetch({
                path: '/cjc/v1/recipes?per_page=100',
            });
            setAvailableRecipes(recipes);
        } catch (err) {
            console.error('Error loading recipes:', err);
        }
    };

    const createNewRecipe = async () => {
        setIsLoading(true);
        setError(null);
        try {
            // Create a new recipe post
            const response = await apiFetch({
                path: '/wp/v2/cjc-recipes',
                method: 'POST',
                data: {
                    title: __('New Recipe', 'suspended-flavor-child'),
                    status: 'publish',
                },
            });
            setAttributes({ recipeId: response.id });
            setRecipeData({ ...DEFAULT_RECIPE_DATA, title: response.title.rendered });
            setHasChanges(false);
            createSuccessNotice(__('Recipe created!', 'suspended-flavor-child'), {
                type: 'snackbar',
            });
        } catch (err) {
            setError(__('Failed to create recipe.', 'suspended-flavor-child'));
            console.error('Error creating recipe:', err);
        }
        setIsLoading(false);
    };

    const saveRecipeData = async () => {
        if (!recipeId) return;

        setIsSaving(true);
        setError(null);
        try {
            // Update the post title
            await apiFetch({
                path: `/wp/v2/cjc-recipes/${recipeId}`,
                method: 'POST',
                data: {
                    title: recipeData.title,
                    meta: {
                        _cjc_recipe_description: recipeData.description,
                        _cjc_recipe_prep_time: recipeData.prep_time,
                        _cjc_recipe_cook_time: recipeData.cook_time,
                        _cjc_recipe_total_time: recipeData.total_time,
                        _cjc_recipe_yield: recipeData.yield,
                        _cjc_recipe_yield_number: recipeData.yield_number,
                        _cjc_recipe_category: recipeData.category,
                        _cjc_recipe_cuisine: recipeData.cuisine,
                        _cjc_recipe_method: recipeData.method,
                        _cjc_recipe_diet: recipeData.diet,
                        _cjc_recipe_keywords: recipeData.keywords,
                        _cjc_recipe_ingredients: JSON.stringify(recipeData.ingredients),
                        _cjc_recipe_instructions: JSON.stringify(recipeData.instructions),
                        _cjc_recipe_notes: recipeData.notes,
                        _cjc_recipe_video_url: recipeData.video_url,
                        _cjc_recipe_serving_size: recipeData.nutrition.serving_size,
                        _cjc_recipe_calories: recipeData.nutrition.calories,
                        _cjc_recipe_sugar: recipeData.nutrition.sugar,
                        _cjc_recipe_sodium: recipeData.nutrition.sodium,
                        _cjc_recipe_fat: recipeData.nutrition.fat,
                        _cjc_recipe_saturated_fat: recipeData.nutrition.saturated_fat,
                        _cjc_recipe_unsaturated_fat: recipeData.nutrition.unsaturated_fat,
                        _cjc_recipe_trans_fat: recipeData.nutrition.trans_fat,
                        _cjc_recipe_carbohydrates: recipeData.nutrition.carbohydrates,
                        _cjc_recipe_fiber: recipeData.nutrition.fiber,
                        _cjc_recipe_protein: recipeData.nutrition.protein,
                        _cjc_recipe_cholesterol: recipeData.nutrition.cholesterol,
                    },
                },
            });

            // Update featured image
            if (recipeData.image_id) {
                await apiFetch({
                    path: `/wp/v2/cjc-recipes/${recipeId}`,
                    method: 'POST',
                    data: {
                        featured_media: recipeData.image_id,
                    },
                });
            }

            setHasChanges(false);
            createSuccessNotice(__('Recipe saved!', 'suspended-flavor-child'), {
                type: 'snackbar',
            });
        } catch (err) {
            setError(__('Failed to save recipe.', 'suspended-flavor-child'));
            createErrorNotice(__('Failed to save recipe.', 'suspended-flavor-child'), {
                type: 'snackbar',
            });
            console.error('Error saving recipe:', err);
        }
        setIsSaving(false);
    };

    const updateRecipeField = (field, value) => {
        setRecipeData((prev) => ({
            ...prev,
            [field]: value,
        }));
        setHasChanges(true);
    };

    const updateNutritionField = (field, value) => {
        setRecipeData((prev) => ({
            ...prev,
            nutrition: {
                ...prev.nutrition,
                [field]: value,
            },
        }));
        setHasChanges(true);
    };

    // Show placeholder if no recipe selected
    if (!recipeId) {
        return (
            <div className="cjc-recipe-editor-placeholder">
                <Placeholder
                    icon="food"
                    label={__('CJC Recipe', 'suspended-flavor-child')}
                    instructions={__(
                        'Create a new recipe or select an existing one.',
                        'suspended-flavor-child'
                    )}
                >
                    <Button variant="primary" onClick={createNewRecipe} disabled={isLoading}>
                        {isLoading ? <Spinner /> : __('Create New Recipe', 'suspended-flavor-child')}
                    </Button>
                    <Button variant="secondary" onClick={() => setShowRecipeSelector(true)}>
                        {__('Select Existing Recipe', 'suspended-flavor-child')}
                    </Button>
                </Placeholder>

                {showRecipeSelector && (
                    <Modal
                        title={__('Select Recipe', 'suspended-flavor-child')}
                        onRequestClose={() => setShowRecipeSelector(false)}
                    >
                        <div className="cjc-recipe-selector">
                            {availableRecipes.length === 0 ? (
                                <p>{__('No recipes found.', 'suspended-flavor-child')}</p>
                            ) : (
                                <ul className="cjc-recipe-list">
                                    {availableRecipes.map((recipe) => (
                                        <li key={recipe.id}>
                                            <Button
                                                variant="link"
                                                onClick={() => {
                                                    setAttributes({ recipeId: recipe.id });
                                                    setShowRecipeSelector(false);
                                                }}
                                            >
                                                {recipe.title || __('Untitled Recipe', 'suspended-flavor-child')}
                                            </Button>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    </Modal>
                )}
            </div>
        );
    }

    if (isLoading) {
        return (
            <div className="cjc-recipe-editor-loading">
                <Spinner />
                <p>{__('Loading recipe...', 'suspended-flavor-child')}</p>
            </div>
        );
    }

    return (
        <>
            <BlockControls>
                <ToolbarGroup>
                    <ToolbarButton
                        icon="update"
                        label={__('Save Recipe', 'suspended-flavor-child')}
                        onClick={saveRecipeData}
                        disabled={!hasChanges || isSaving}
                    />
                    <ToolbarButton
                        icon="randomize"
                        label={__('Change Recipe', 'suspended-flavor-child')}
                        onClick={() => setShowRecipeSelector(true)}
                    />
                </ToolbarGroup>
            </BlockControls>

            <InspectorControls>
                <Panel>
                    <PanelBody title={__('Recipe Details', 'suspended-flavor-child')} initialOpen>
                        <TextControl
                            label={__('Category', 'suspended-flavor-child')}
                            value={recipeData.category}
                            onChange={(value) => updateRecipeField('category', value)}
                            placeholder={__('e.g., Main Course, Dessert', 'suspended-flavor-child')}
                        />
                        <TextControl
                            label={__('Cuisine', 'suspended-flavor-child')}
                            value={recipeData.cuisine}
                            onChange={(value) => updateRecipeField('cuisine', value)}
                            placeholder={__('e.g., Hawaiian, Asian', 'suspended-flavor-child')}
                        />
                        <TextControl
                            label={__('Method', 'suspended-flavor-child')}
                            value={recipeData.method}
                            onChange={(value) => updateRecipeField('method', value)}
                            placeholder={__('e.g., Grilling, Baking', 'suspended-flavor-child')}
                        />
                        <SelectControl
                            label={__('Diet', 'suspended-flavor-child')}
                            value={recipeData.diet}
                            onChange={(value) => updateRecipeField('diet', value)}
                            options={[
                                { label: __('N/A', 'suspended-flavor-child'), value: '' },
                                { label: __('Diabetic', 'suspended-flavor-child'), value: 'Diabetic' },
                                { label: __('Gluten Free', 'suspended-flavor-child'), value: 'Gluten Free' },
                                { label: __('Halal', 'suspended-flavor-child'), value: 'Halal' },
                                { label: __('Hindu', 'suspended-flavor-child'), value: 'Hindu' },
                                { label: __('Kosher', 'suspended-flavor-child'), value: 'Kosher' },
                                { label: __('Low Calorie', 'suspended-flavor-child'), value: 'Low Calorie' },
                                { label: __('Low Fat', 'suspended-flavor-child'), value: 'Low Fat' },
                                { label: __('Low Lactose', 'suspended-flavor-child'), value: 'Low Lactose' },
                                { label: __('Low Salt', 'suspended-flavor-child'), value: 'Low Salt' },
                                { label: __('Vegan', 'suspended-flavor-child'), value: 'Vegan' },
                                { label: __('Vegetarian', 'suspended-flavor-child'), value: 'Vegetarian' },
                            ]}
                        />
                        <TextControl
                            label={__('Keywords', 'suspended-flavor-child')}
                            value={recipeData.keywords}
                            onChange={(value) => updateRecipeField('keywords', value)}
                            placeholder={__('summer, quick, easy', 'suspended-flavor-child')}
                            help={__('Comma-separated keywords for SEO', 'suspended-flavor-child')}
                        />
                    </PanelBody>

                    <PanelBody title={__('Video', 'suspended-flavor-child')} initialOpen={false}>
                        <TextControl
                            label={__('Video URL', 'suspended-flavor-child')}
                            value={recipeData.video_url}
                            onChange={(value) => updateRecipeField('video_url', value)}
                            placeholder="https://youtube.com/watch?v=..."
                        />
                    </PanelBody>

                    <NutritionEditor
                        nutrition={recipeData.nutrition}
                        onChange={updateNutritionField}
                    />
                </Panel>
            </InspectorControls>

            <div className="cjc-recipe-editor">
                {error && (
                    <Notice status="error" isDismissible onRemove={() => setError(null)}>
                        {error}
                    </Notice>
                )}

                {hasChanges && (
                    <Notice status="warning" isDismissible={false}>
                        {__('You have unsaved changes.', 'suspended-flavor-child')}
                        <Button
                            variant="primary"
                            onClick={saveRecipeData}
                            disabled={isSaving}
                            style={{ marginLeft: '10px' }}
                        >
                            {isSaving ? <Spinner /> : __('Save Recipe', 'suspended-flavor-child')}
                        </Button>
                    </Notice>
                )}

                {/* Recipe Header */}
                <div className="cjc-recipe-editor-header">
                    <div className="cjc-recipe-editor-image">
                        <MediaUploadCheck>
                            <MediaUpload
                                onSelect={(media) => {
                                    updateRecipeField('image_id', media.id);
                                    updateRecipeField('image_url', media.url);
                                }}
                                allowedTypes={['image']}
                                value={recipeData.image_id}
                                render={({ open }) => (
                                    <div className="cjc-recipe-editor-image-wrapper" onClick={open}>
                                        {recipeData.image_url ? (
                                            <img src={recipeData.image_url} alt="" />
                                        ) : (
                                            <div className="cjc-recipe-editor-image-placeholder">
                                                <span className="dashicons dashicons-format-image"></span>
                                                <span>{__('Click to add image', 'suspended-flavor-child')}</span>
                                            </div>
                                        )}
                                    </div>
                                )}
                            />
                        </MediaUploadCheck>
                    </div>
                    <div className="cjc-recipe-editor-title-area">
                        <TextControl
                            label={__('Recipe Title', 'suspended-flavor-child')}
                            value={recipeData.title}
                            onChange={(value) => updateRecipeField('title', value)}
                            placeholder={__('Enter recipe title...', 'suspended-flavor-child')}
                            className="cjc-recipe-editor-title-input"
                        />
                        <TextareaControl
                            label={__('Description', 'suspended-flavor-child')}
                            value={recipeData.description}
                            onChange={(value) => updateRecipeField('description', value)}
                            placeholder={__('A brief description of this recipe...', 'suspended-flavor-child')}
                            rows={3}
                        />
                    </div>
                </div>

                {/* Times */}
                <TimesEditor
                    prepTime={recipeData.prep_time}
                    cookTime={recipeData.cook_time}
                    totalTime={recipeData.total_time}
                    recipeYield={recipeData.yield}
                    yieldNumber={recipeData.yield_number}
                    onChange={updateRecipeField}
                />

                {/* Ingredients */}
                <div className="cjc-recipe-editor-section">
                    <h3>{__('Ingredients', 'suspended-flavor-child')}</h3>
                    <IngredientsEditor
                        ingredients={recipeData.ingredients}
                        onChange={(value) => updateRecipeField('ingredients', value)}
                    />
                </div>

                {/* Instructions */}
                <div className="cjc-recipe-editor-section">
                    <h3>{__('Instructions', 'suspended-flavor-child')}</h3>
                    <InstructionsEditor
                        instructions={recipeData.instructions}
                        onChange={(value) => updateRecipeField('instructions', value)}
                    />
                </div>

                {/* Notes */}
                <div className="cjc-recipe-editor-section">
                    <h3>{__('Notes', 'suspended-flavor-child')}</h3>
                    <TextareaControl
                        value={recipeData.notes}
                        onChange={(value) => updateRecipeField('notes', value)}
                        placeholder={__('Add tips, variations, or storage instructions...', 'suspended-flavor-child')}
                        rows={4}
                    />
                </div>
            </div>

            {showRecipeSelector && (
                <Modal
                    title={__('Select Recipe', 'suspended-flavor-child')}
                    onRequestClose={() => setShowRecipeSelector(false)}
                >
                    <div className="cjc-recipe-selector">
                        {availableRecipes.length === 0 ? (
                            <p>{__('No recipes found.', 'suspended-flavor-child')}</p>
                        ) : (
                            <ul className="cjc-recipe-list">
                                {availableRecipes.map((recipe) => (
                                    <li key={recipe.id}>
                                        <Button
                                            variant={recipe.id === recipeId ? 'primary' : 'link'}
                                            onClick={() => {
                                                setAttributes({ recipeId: recipe.id });
                                                setShowRecipeSelector(false);
                                            }}
                                        >
                                            {recipe.title || __('Untitled Recipe', 'suspended-flavor-child')}
                                        </Button>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </Modal>
            )}
        </>
    );
}
