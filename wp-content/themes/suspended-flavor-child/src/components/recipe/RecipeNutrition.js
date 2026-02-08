/**
 * RecipeNutrition Component
 *
 * Displays the nutrition information panel.
 */

const NUTRITION_FIELDS = [
    { key: 'serving_size', label: 'Serving Size' },
    { key: 'calories', label: 'Calories' },
    { key: 'fat', label: 'Total Fat' },
    { key: 'saturated_fat', label: 'Saturated Fat', indent: true },
    { key: 'unsaturated_fat', label: 'Unsaturated Fat', indent: true },
    { key: 'trans_fat', label: 'Trans Fat', indent: true },
    { key: 'cholesterol', label: 'Cholesterol' },
    { key: 'sodium', label: 'Sodium' },
    { key: 'carbohydrates', label: 'Total Carbohydrates' },
    { key: 'fiber', label: 'Dietary Fiber', indent: true },
    { key: 'sugar', label: 'Sugars', indent: true },
    { key: 'protein', label: 'Protein' },
];

export default function RecipeNutrition({ nutrition = {} }) {
    // Check if we have any nutrition data
    const hasData = NUTRITION_FIELDS.some((field) => nutrition[field.key]);

    if (!hasData) {
        return null;
    }

    return (
        <section className="cjc-recipe-nutrition">
            <h3 className="cjc-recipe-section-title">Nutrition Facts</h3>
            <p className="cjc-recipe-nutrition-note">Per serving</p>

            <dl className="cjc-recipe-nutrition-list">
                {NUTRITION_FIELDS.map((field) => {
                    const value = nutrition[field.key];
                    if (!value) return null;

                    return (
                        <div
                            key={field.key}
                            className={`cjc-recipe-nutrition-item ${field.indent ? 'is-indented' : ''}`}
                        >
                            <dt className="cjc-recipe-nutrition-label">{field.label}</dt>
                            <dd className="cjc-recipe-nutrition-value">{value}</dd>
                        </div>
                    );
                })}
            </dl>

            <p className="cjc-recipe-nutrition-disclaimer">
                * Percent Daily Values are based on a 2,000 calorie diet.
            </p>
        </section>
    );
}
