/**
 * Floating Food Particles Background
 */

export default function FloatingParticles() {
    const particles = [
        { emoji: '🍍', delay: 0, duration: 15, left: 10 },
        { emoji: '🥥', delay: 3, duration: 18, left: 25 },
        { emoji: '🌺', delay: 6, duration: 12, left: 45 },
        { emoji: '🍹', delay: 2, duration: 20, left: 65 },
        { emoji: '🐟', delay: 8, duration: 16, left: 80 },
        { emoji: '🌴', delay: 4, duration: 14, left: 90 },
    ];

    return (
        <div className="cjc-floating-particles">
            {particles.map((particle, index) => (
                <span
                    key={index}
                    className="cjc-particle"
                    style={{
                        left: `${particle.left}%`,
                        animationDelay: `${particle.delay}s`,
                        animationDuration: `${particle.duration}s`,
                    }}
                >
                    {particle.emoji}
                </span>
            ))}
        </div>
    );
}
