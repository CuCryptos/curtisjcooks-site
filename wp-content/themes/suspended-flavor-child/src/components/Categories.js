/**
 * Category Pills Section
 */

import { useState, useRef, useEffect } from 'react';

function CategoryPill({ icon, name, count, link, isActive, onClick, delay }) {
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
            className={`cjc-category-pill ${isActive ? 'active' : ''} ${isVisible ? 'is-visible' : ''}`}
            style={{ transitionDelay: `${delay * 100}ms` }}
            onClick={onClick}
        >
            <span className="cjc-pill-icon">{icon}</span>
            <div className="cjc-pill-info">
                <span className="cjc-pill-name">{name}</span>
                <span className="cjc-pill-count">{count} recipes</span>
            </div>
        </a>
    );
}

export default function Categories({ data }) {
    const [activeCategory, setActiveCategory] = useState('all');

    const categories = data?.categories || [
        { id: 'all', icon: '🌺', name: 'All Recipes', count: 50, link: '/recipes/' },
        { id: 'breakfast', icon: '🍳', name: 'Breakfast', count: 8, link: '/category/hawaiian-breakfast/' },
        { id: 'drinks', icon: '🍹', name: 'Island Drinks', count: 12, link: '/category/island-drinks/' },
        { id: 'seafood', icon: '🐟', name: 'Poke & Seafood', count: 10, link: '/category/poke-seafood/' },
        { id: 'comfort', icon: '🍖', name: 'Comfort Food', count: 15, link: '/category/island-comfort/' },
        { id: 'treats', icon: '🍰', name: 'Tropical Treats', count: 8, link: '/category/tropical-treats/' },
    ];

    return (
        <section className="cjc-categories-section">
            <div className="cjc-section-header">
                <h2>Explore by Category</h2>
                <p className="cjc-section-subtitle">Find your next favorite Hawaiian dish</p>
            </div>

            <div className="cjc-category-pills">
                {categories.map((cat, index) => (
                    <CategoryPill
                        key={cat.id}
                        {...cat}
                        isActive={activeCategory === cat.id}
                        delay={index}
                        onClick={(e) => {
                            if (cat.id === 'all') {
                                e.preventDefault();
                                setActiveCategory(cat.id);
                            }
                        }}
                    />
                ))}
            </div>
        </section>
    );
}
