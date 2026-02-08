/**
 * Newsletter Signup Section
 */

import { useState, useRef, useEffect } from 'react';

export default function Newsletter() {
    const [email, setEmail] = useState('');
    const [isVisible, setIsVisible] = useState(false);
    const ref = useRef(null);

    useEffect(() => {
        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setIsVisible(true);
                }
            },
            { threshold: 0.2 }
        );

        if (ref.current) {
            observer.observe(ref.current);
        }

        return () => observer.disconnect();
    }, []);

    const handleSubmit = (e) => {
        e.preventDefault();
        // Handle newsletter signup
        console.log('Newsletter signup:', email);
        alert('Mahalo! You\'ve joined the Ohana!');
        setEmail('');
    };

    return (
        <section ref={ref} className="cjc-newsletter-section">
            <div className="cjc-newsletter-bg">
                <span>🌺</span>
                <span>🍍</span>
            </div>

            <div className={`cjc-newsletter-content ${isVisible ? 'is-visible' : ''}`}>
                <h2>Join the Ohana! 🌺</h2>
                <p className="cjc-newsletter-subtitle">
                    Get weekly Hawaiian recipes, cooking tips, and island inspiration
                    delivered straight to your inbox.
                </p>

                <form className="cjc-newsletter-form" onSubmit={handleSubmit}>
                    <input
                        type="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        placeholder="Enter your email"
                        required
                    />
                    <button type="submit">Subscribe Free</button>
                </form>

                <p className="cjc-newsletter-note">
                    Join 12,000+ food lovers. Unsubscribe anytime.
                </p>
            </div>
        </section>
    );
}
