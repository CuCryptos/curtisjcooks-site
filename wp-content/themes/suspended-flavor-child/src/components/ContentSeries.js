/**
 * Content Series Section
 * Displays the four main content series with their latest posts
 */

import { useState, useRef, useEffect } from 'react';

function SeriesCard({ series, delay }) {
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
            href={series.link}
            className={`cjc-series-card ${isVisible ? 'is-visible' : ''}`}
            style={{
                transitionDelay: `${delay * 150}ms`,
                '--series-color': series.color
            }}
        >
            <div className="cjc-series-icon">{series.icon}</div>
            <div className="cjc-series-content">
                <h3 className="cjc-series-title">{series.name}</h3>
                <p className="cjc-series-description">{series.description}</p>
                <span className="cjc-series-count">{series.count} posts</span>
            </div>
            <div className="cjc-series-arrow">→</div>
        </a>
    );
}

export default function ContentSeries({ data }) {
    const series = data?.series || [
        {
            id: 'aloha-friday',
            icon: '🌺',
            name: 'Aloha Friday',
            description: 'End your week with island-inspired dishes and good vibes',
            count: 3,
            color: '#FF6B6B',
            link: '/series/aloha-friday/'
        },
        {
            id: 'plate-lunch',
            icon: '🍱',
            name: 'Plate Lunch of the Week',
            description: 'Classic Hawaiian plate lunch recipes every week',
            count: 3,
            color: '#4ECDC4',
            link: '/series/plate-lunch-of-the-week/'
        },
        {
            id: 'talk-story',
            icon: '🌴',
            name: 'Talk Story',
            description: 'Personal stories and memories from the islands',
            count: 3,
            color: '#45B7D1',
            link: '/series/talk-story/'
        },
        {
            id: 'cooking-101',
            icon: '👨‍🍳',
            name: 'Hawaiian Cooking 101',
            description: 'Master the basics of Hawaiian cuisine',
            count: 2,
            color: '#96CEB4',
            link: '/series/hawaiian-cooking-101/'
        }
    ];

    return (
        <section className="cjc-series-section">
            <div className="cjc-section-header">
                <h2>Content Series</h2>
                <p className="cjc-section-subtitle">Dive deep into Hawaiian food culture</p>
            </div>

            <div className="cjc-series-grid">
                {series.map((s, index) => (
                    <SeriesCard
                        key={s.id}
                        series={s}
                        delay={index}
                    />
                ))}
            </div>
        </section>
    );
}
