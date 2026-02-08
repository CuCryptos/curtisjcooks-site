/**
 * Stats Section
 */

const sectionStyle = {
    background: 'linear-gradient(135deg, #f97316 0%, #fb923c 100%)',
    padding: '64px 24px',
    textAlign: 'center'
};

const containerStyle = {
    maxWidth: '1280px',
    margin: '0 auto',
    display: 'grid',
    gridTemplateColumns: 'repeat(3, 1fr)',
    gap: '48px'
};

const itemStyle = {
    color: 'white'
};

const numberStyle = {
    fontSize: '3rem',
    fontWeight: '700',
    marginBottom: '8px'
};

const labelStyle = {
    fontSize: '1rem',
    opacity: '0.9'
};

export default function Stats({ data }) {
    const stats = data?.stats || { recipes: 50, readers: 12000, rating: 4.9 };

    return (
        <section style={sectionStyle}>
            <div style={containerStyle}>
                <div style={itemStyle}>
                    <div style={numberStyle}>{stats.recipes}+</div>
                    <div style={labelStyle}>Hawaiian Recipes</div>
                </div>
                <div style={itemStyle}>
                    <div style={numberStyle}>{stats.readers.toLocaleString()}+</div>
                    <div style={labelStyle}>Monthly Readers</div>
                </div>
                <div style={itemStyle}>
                    <div style={numberStyle}>{stats.rating} ★</div>
                    <div style={labelStyle}>Average Rating</div>
                </div>
            </div>
        </section>
    );
}
