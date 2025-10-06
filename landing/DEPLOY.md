# ArgusPAM Landing Page - Deployment Guide

Complete deployment documentation for ArgusPAM landing page.

## Files Overview

- **index.html** - Original simple coming soon page
- **index2.html** - Initial redesigned version with modern layout
- **index3.html** - **RECOMMENDED** - Full-featured production-ready page with all enhancements
- **images/** - Directory for product screenshots and feature images
- **ai-prompts/** - AI prompts for generating required images

## ✨ Features (index3.html)

### Design & UX
✅ Modern, responsive design inspired by professional SaaS landing pages
✅ Scroll animations with Intersection Observer API
✅ Smooth transitions and hover effects
✅ Mobile-first responsive design
✅ Fixed header with scroll effects
✅ Accessibility features (ARIA labels, focus states, keyboard navigation)

### Content Sections
✅ Hero section with compelling value proposition
✅ Problem section highlighting pain points
✅ 3 detailed feature sections with benefits
✅ Comparison table (ArgusPAM vs Traditional PAM)
✅ Technical architecture diagram
✅ Benefits grid with specific outcomes
✅ How It Works (4-step process)
✅ FAQ section (6 common questions)
✅ Interactive waitlist signup form
✅ Professional footer

### Technical
✅ SEO optimized (meta tags, Open Graph, Twitter Cards)
✅ Google Analytics integration with event tracking
✅ SVG favicon (data URI)
✅ Lazy loading for images
✅ Focus management for accessibility
✅ Semantic HTML5
✅ Zero external dependencies (except fonts)

### Database Support
- PostgreSQL
- MySQL
- MongoDB
- Redis
- More coming soon...

## 📦 Deployment

### Option 1: S3 Static Hosting (Recommended)

1. **Prepare files:**
   ```bash
   # Copy index3.html as index.html
   cp index3.html index.html

   # Generate images using the placeholder generator
   open images/placeholder-generator.html
   # Right-click each canvas and "Save image as..." to images/ folder
   ```

2. **Replace GTM_ID:**
   ```bash
   # Replace GTM_ID placeholder with your actual Google Tag Manager ID
   sed -i '' 's/GTM_ID/GTM-XXXXXXX/g' index.html
   ```

3. **Deploy to S3:**
   ```bash
   aws s3 sync . s3://your-bucket-name/ \
     --exclude ".git/*" \
     --exclude "*.md" \
     --exclude "ai-prompts/*" \
     --exclude "images/placeholder-generator.html" \
     --exclude "index.html" \
     --exclude "index2.html" \
     --exclude "logo.html"

   # Upload index.html separately
   aws s3 cp index.html s3://your-bucket-name/index.html \
     --content-type "text/html" \
     --cache-control "max-age=300"
   ```

4. **Configure S3 bucket:**
   - Enable static website hosting
   - Set index document to `index.html`
   - Configure bucket policy for public read access
   - Set up CloudFront for HTTPS and caching

### Option 2: Netlify/Vercel

1. **Create `netlify.toml` or `vercel.json`:**
   ```toml
   # netlify.toml
   [build]
     publish = "."

   [[redirects]]
     from = "/*"
     to = "/index.html"
     status = 200
   ```

2. **Deploy:**
   ```bash
   # Netlify
   netlify deploy --prod

   # Vercel
   vercel --prod
   ```

### Option 3: GitHub Pages

1. **Push to repository:**
   ```bash
   git add index.html images/
   git commit -m "Deploy landing page"
   git push origin main
   ```

2. **Enable GitHub Pages:**
   - Go to repository Settings → Pages
   - Select branch: `main`
   - Select folder: `/ (root)`
   - Save

## 🎨 Image Generation

### Using AI Tools

Use the prompts in `ai-prompts/` folder with:
- **Midjourney**: `/imagine <paste prompt>`
- **DALL-E 3**: Paste prompt directly
- **Stable Diffusion**: Use prompt with appropriate parameters

Required images:
1. `hero-dashboard.png` (1200x800px) - Dashboard mockup
2. `feature-zero-privilege.png` (800x600px) - Workflow diagram
3. `feature-ai-detection.png` (800x600px) - AI detection visualization
4. `feature-audit-trail.png` (800x600px) - Audit log interface

### Using Placeholder Generator

1. Open `images/placeholder-generator.html` in a browser
2. Right-click each canvas
3. Select "Save image as..."
4. Save with the corresponding filename

### Using Design Tools

Create mockups using:
- **Figma**: Design custom dashboards and interfaces
- **Sketch**: Create product screenshots
- **Photoshop**: Design feature visualizations

## ⚙️ Customization

### Colors

Update CSS variables in `<style>` section:

```css
:root {
    --primary: #0F172A;        /* Dark blue */
    --accent: #3B82F6;         /* Blue */
    --secondary: #10B981;      /* Green */
    --danger: #EF4444;         /* Red */
}
```

### Content

#### Update Launch Date
Search for "Q1 2026" and replace with your launch timeline.

#### Update Database Support
Edit the tech stack section in hero:
```html
<div class="tech-icons">
    <span class="tech-icon">PostgreSQL</span>
    <span class="tech-icon">MySQL</span>
    <!-- Add more databases -->
</div>
```

#### Update FAQ
Add/remove FAQ items in the FAQ section.

### Analytics

#### Google Analytics Events Tracked:
- CTA clicks (hero GitHub button)
- Waitlist form submissions
- Social link clicks
- Button interactions

#### Add Custom Events:
```javascript
trackEvent('Category', 'Action', 'Label');
```

### Waitlist Form

Currently using simulated submission. To integrate with real backend:

1. **Replace the setTimeout in script section with:**
```javascript
try {
    const response = await fetch('https://your-api.com/waitlist', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
    });

    if (response.ok) {
        formMessage.className = 'form-message success';
        formMessage.textContent = '🎉 Success! You\'re on the waitlist.';
    }
} catch (error) {
    formMessage.className = 'form-message error';
    formMessage.textContent = '❌ Please try again.';
}
```

2. **Popular integrations:**
   - **Formspree**: `https://formspree.io/f/your-form-id`
   - **Netlify Forms**: Add `data-netlify="true"` to form
   - **ConvertKit**: Use ConvertKit API
   - **Mailchimp**: Use Mailchimp API

## 🚀 Performance Optimization

### Before deploying:

1. **Minify HTML:**
   ```bash
   npm install -g html-minifier
   html-minifier --collapse-whitespace --remove-comments index.html -o index.min.html
   ```

2. **Optimize images:**
   ```bash
   # Install imagemin
   npm install -g imagemin-cli imagemin-pngquant

   # Optimize PNGs
   imagemin images/*.png --out-dir=images-optimized --plugin=pngquant
   ```

3. **Enable compression:**
   - CloudFront: Enable automatic compression
   - S3: Upload with `Content-Encoding: gzip`
   - Netlify/Vercel: Automatic compression enabled

## 🌐 Browser Support

- Chrome/Edge (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- Mobile Safari iOS 12+
- Chrome Android (last 2 versions)

## ♿ Accessibility

- WCAG 2.1 Level AA compliant
- Keyboard navigation supported
- Screen reader friendly (ARIA labels)
- Focus indicators on all interactive elements
- Semantic HTML5 structure

## 📝 License

© 2025 ArgusPAM. All rights reserved.

## 💬 Support

For questions or issues:
- GitHub: https://github.com/lunzai/arguspam
- Issues: https://github.com/lunzai/arguspam/issues

## 📋 Changelog

### v3.0 (Current - index3.html)
- Added FAQ section
- Added comparison table
- Added technical architecture diagram
- Added interactive waitlist form
- Added scroll animations
- Improved accessibility (ARIA labels, focus states)
- Added analytics event tracking
- Added technical depth (database types)
- Added SVG favicon
- Mobile responsive improvements

### v2.0 (index2.html)
- Redesigned with modern layout
- Added problem-solution structure
- Added detailed feature sections
- Added benefits grid
- Improved responsive design

### v1.0 (index.html)
- Initial coming soon page
- Basic design and copy
