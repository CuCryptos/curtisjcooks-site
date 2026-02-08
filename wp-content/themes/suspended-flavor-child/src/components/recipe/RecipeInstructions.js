/**
 * RecipeInstructions Component
 *
 * Displays the step-by-step instructions.
 */

export default function RecipeInstructions({ instructions = [] }) {
    if (!instructions || instructions.length === 0) {
        return null;
    }

    // Calculate step numbers across groups
    let stepCounter = 0;

    return (
        <section className="cjc-recipe-instructions">
            <h3 className="cjc-recipe-section-title">Instructions</h3>

            {instructions.map((group, groupIndex) => {
                return (
                    <div key={groupIndex} className="cjc-recipe-instruction-group">
                        {group.title && (
                            <h4 className="cjc-recipe-instruction-group-title">{group.title}</h4>
                        )}

                        <ol className="cjc-recipe-instruction-list" start={stepCounter + 1}>
                            {group.steps?.map((step, stepIndex) => {
                                stepCounter++;
                                return (
                                    <li key={stepIndex} className="cjc-recipe-instruction-step">
                                        <span className="cjc-recipe-step-number" aria-hidden="true">
                                            {stepCounter}
                                        </span>
                                        <div
                                            className="cjc-recipe-step-text"
                                            dangerouslySetInnerHTML={{ __html: step.text }}
                                        />
                                    </li>
                                );
                            })}
                        </ol>
                    </div>
                );
            })}
        </section>
    );
}
