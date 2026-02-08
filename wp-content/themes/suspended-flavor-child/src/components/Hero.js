/**
 * Hero Section Component
 */

import { useState, useEffect, useRef } from 'react';

export default function Hero({ data }) {
    const [isVisible, setIsVisible] = useState(false);
    const heroRef = useRef(null);

    useEffect(() => {
        const timer = setTimeout(() => setIsVisible(true), 100);
        return () => clearTimeout(timer);
    }, []);

    const heroImage = data?.images?.hero || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800';

    return (
        <section ref={heroRef} className="cjc-hero-section">
            <div className="cjc-hero-bg-animation" />

            <div className="cjc-hero-container">
                {/* Text Content */}
                <div className={`cjc-hero-text ${isVisible ? 'is-visible' : ''}`}>
                    <div className="cjc-hero-badge">
                        <span className="cjc-badge-icon">🔥</span>
                        <span>Authentic Hawaiian Recipes</span>
                    </div>

                    <h1>
                        Taste the
                        <span className="cjc-gradient-text">Aloha Spirit</span>
                    </h1>

                    <p className="cjc-hero-description">
                        Bring the flavors of Hawaii to your kitchen with authentic recipes,
                        local tips, and island-inspired cooking adventures.
                    </p>

                    <div className="cjc-hero-buttons">
                        <a href="/recipes/" className="cjc-btn-primary">
                            Explore Recipes 🌺
                        </a>
                        <a href="#recipes" className="cjc-btn-secondary">
                            Watch Videos ▶️
                        </a>
                    </div>
                </div>

                {/* Hero Image Stack */}
                <div className={`cjc-hero-image-stack ${isVisible ? 'is-visible' : ''}`}>
                    <div className="cjc-hero-main-image">
                        <img src={heroImage} alt="Hawaiian Food" />
                        <div className="cjc-hero-image-overlay">
                            <div>
                                <p className="cjc-overlay-title">Fresh Ahi Poke</p>
                                <p className="cjc-overlay-subtitle">Ready in 15 minutes</p>
                            </div>
                            <div className="cjc-stars">★★★★★</div>
                        </div>
                    </div>

                    {/* Floating Cards */}
                    <div className="cjc-floating-card top-right">🍹</div>
                    <div className="cjc-floating-card bottom-left">
                        <p className="cjc-card-number">50+</p>
                        <p className="cjc-card-label">Recipes</p>
                    </div>
                </div>
            </div>

            {/* Scroll Indicator */}
            <div className="cjc-scroll-indicator">
                <span>Scroll to explore</span>
                <span>↓</span>
            </div>
        </section>
    );
}
