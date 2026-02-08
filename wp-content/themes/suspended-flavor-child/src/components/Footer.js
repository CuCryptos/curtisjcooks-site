/**
 * Footer Component
 */

export default function Footer() {
    const currentYear = new Date().getFullYear();

    const socialLinks = [
        { icon: '📸', label: 'Instagram', url: '#' },
        { icon: '📌', label: 'Pinterest', url: '#' },
        { icon: '▶️', label: 'YouTube', url: '#' },
        { icon: '✉️', label: 'Email', url: 'mailto:hello@curtisjcooks.com' },
    ];

    return (
        <footer className="cjc-footer">
            <div className="cjc-footer-container">
                <div className="cjc-footer-logo">
                    <span>🌺</span>
                    <span>CurtisJCooks</span>
                </div>

                <div className="cjc-footer-social">
                    {socialLinks.map((link, index) => (
                        <a
                            key={index}
                            href={link.url}
                            className="cjc-social-icon"
                            aria-label={link.label}
                        >
                            {link.icon}
                        </a>
                    ))}
                </div>
            </div>

            <div className="cjc-footer-bottom">
                <p>© {currentYear} CurtisJCooks. Made with Aloha 🌺</p>
            </div>
        </footer>
    );
}
