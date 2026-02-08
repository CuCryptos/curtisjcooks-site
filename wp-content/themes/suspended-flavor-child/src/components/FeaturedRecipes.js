/**
 * Featured Recipes Section
 */

import { useState, useRef, useEffect } from 'react';

function RecipeCard({ image, title, category, time, link, delay }) {
    const [isHovered, setIsHovered] = useState(false);
    const [isVisible, setIsVisible] = useState(false);
    const ref = useRef(null);

    useEffect(() => {
        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setIsVisible(true);
                }
            },
            { threshold: 0.1 }
        );

        if (ref.current) {
            observer.observe(ref.current);
        }

        return () => observer.disconnect();
    }, []);

    return (
        <a
            ref={ref}
            href={link}
            className={`cjc-recipe-card ${isVisible ? 'is-visible' : ''} ${isHovered ? 'is-hovered' : ''}`}
            style={{ transitionDelay: `${delay * 150}ms` }}
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
        >
            <div className="cjc-card-image">
                <img src={image} alt={title} />
            </div>
            <div className="cjc-card-overlay" />
            <div className="cjc-card-content">
                <span className="cjc-card-category">{category}</span>
                <h3 className="cjc-card-title">{title}</h3>
                <div className="cjc-card-meta">
                    <span>⏱️ {time}</span>
                    <span>•</span>
                    <span>View Recipe →</span>
                </div>
            </div>
        </a>
    );
}

export default function FeaturedRecipes({ recipes }) {
    // Default recipes if none provided
    const displayRecipes = recipes.length > 0 ? recipes : [
        {
            image: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600',
            title: 'Classic Ahi Poke Bowl',
            category: 'Seafood',
            time: '15 min',
            link: '#',
        },
        {
            image: 'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=600',
            title: 'Tropical Mai Tai',
            category: 'Drinks',
            time: '5 min',
            link: '#',
        },
        {
            image: 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=600',
            title: 'Hawaiian Pancakes',
            category: 'Breakfast',
            time: '20 min',
            link: '#',
        },
        {
            image: 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600',
            title: 'Kalua Pork Sliders',
            category: 'Comfort',
            time: '4 hrs',
            link: '#',
        },
        {
            image: 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=600',
            title: 'Coconut Haupia',
            category: 'Dessert',
            time: '30 min',
            link: '#',
        },
        {
            image: 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600',
            title: 'Loco Moco',
            category: 'Comfort',
            time: '25 min',
            link: '#',
        },
    ];

    return (
        <section id="recipes" className="cjc-recipes-section">
            <div className="cjc-section-header">
                <div>
                    <h2>Featured Recipes</h2>
                    <p className="cjc-section-subtitle">Handpicked favorites from the islands</p>
                </div>
                <a href="/recipes/" className="cjc-view-all-link">View All →</a>
            </div>

            <div className="cjc-recipes-grid">
                {displayRecipes.map((recipe, index) => (
                    <RecipeCard key={index} {...recipe} delay={index} />
                ))}
            </div>
        </section>
    );
}
