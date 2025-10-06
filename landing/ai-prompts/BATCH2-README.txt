BATCH 2 - AI IMAGE PROMPTS SUMMARY
ArgusPAM Landing Page Graphics & Icons

Generated: October 5, 2025
Style Reference: Soleland landing page (modern 3D illustrations with friendly characters)

===========================================
OVERVIEW
===========================================

This batch contains comprehensive AI prompts for all graphics, illustrations, and icons needed for the ArgusPAM landing page using a modern, friendly 3D illustration style inspired by the Soleland reference.

All prompts are designed to:
- Avoid readable text (visual elements only)
- Maintain consistent friendly 3D style
- Use ArgusPAM brand colors
- Create professional yet approachable imagery
- Work well together as a cohesive set

===========================================
FILES CREATED (8 prompts)
===========================================

MAIN ILLUSTRATIONS:
1. batch2-hero-illustration.txt
   - Hero section main graphic
   - 3D characters with dashboard
   - Size: 16:9 landscape (~1400x900px)
   - Purpose: Primary landing page visual

2. batch2-feature-zero-privilege-graphic.txt
   - Zero standing privilege workflow
   - Hourglass/timer with credential lifecycle
   - Size: 4:3 or square (1200x1200px)
   - Purpose: Feature section 1

3. batch2-feature-ai-detection-graphic.txt
   - AI threat detection visualization
   - Neural network with databases
   - Size: 4:3 or square (1200x1200px)
   - Purpose: Feature section 2

4. batch2-feature-audit-trail-graphic.txt
   - Audit logging and compliance
   - Document stack with security seals
   - Size: 4:3 or square (1200x1200px)
   - Purpose: Feature section 3

5. batch2-comparison-illustration.txt
   - Traditional PAM vs ArgusPAM
   - Split-screen comparison
   - Size: 16:9 landscape (1400x800px)
   - Purpose: Comparison section

6. batch2-architecture-illustration.txt
   - Technical stack diagram
   - 4-layer architecture visualization
   - Size: 16:9 landscape (1600x900px)
   - Purpose: Architecture section

7. batch2-testimonial-illustration.txt
   - Happy customers with feedback
   - Diverse character group
   - Size: Square (1200x1200px)
   - Purpose: Testimonial section

ICONS:
8. batch2-icons.txt
   - ALL 12 icons in single file:
     * 3 Problem section icons (256x256px)
     * 1 Checkmark icon (64x64px)
     * 4 Benefits section icons (256x256px)
     * 4 How It Works step icons (256x256px)

===========================================
ICON BREAKDOWN
===========================================

PROBLEM SECTION (3 icons):
- Icon 1: Standing Privileges (open padlock with warning)
- Icon 2: Credential Sprawl (scattered keys/cards)
- Icon 3: Zero Visibility (obscured eye/database)

FEATURE LISTS:
- Icon 4: Checkmark (green circle with check, 64x64px)

BENEFITS SECTION (4 icons):
- Icon 5: Reduce Attack Surface (shield with checkmark)
- Icon 6: Deploy in Hours (clock with lightning)
- Icon 7: Compliance (certification badge)
- Icon 8: Open Source/Affordable (dollar/heart/piggy bank)

HOW IT WORKS (4 icons):
- Icon 9: Request Access (hand clicking button)
- Icon 10: Automated Approval (gear with checkmark)
- Icon 11: Temporary Credentials (key with timer)
- Icon 12: Monitor & Revoke (eye with monitoring waves)

===========================================
STYLE GUIDELINES
===========================================

Visual Style:
- Friendly 3D isometric illustrations
- Rounded edges (no sharp corners)
- Soft pastel color palette
- Playful but professional
- Modern SaaS aesthetic
- Cute cartoon characters (simplified, minimal features)

Color Palette (ArgusPAM Brand):
- Primary Blue: #3B82F6, #2563EB
- Secondary Green: #10B981, #7FDBCA
- Accent Purple: #8B5CF6, #6366F1
- Warning Orange: #F59E0B, #FBBF24
- Danger Red: #EF4444
- Neutrals: #6B7280, #9CA3AF
- Backgrounds: #FFFEF9, #F8FAFC

Character Design:
- Diverse (varied skin tones, genders, ethnicities)
- Simplified features (minimal eyes/mouth OR abstract)
- Rounded, soft geometric shapes
- Professional casual clothing
- Friendly, approachable expressions
- Slightly oversized heads (cute style)

===========================================
HOW TO USE THESE PROMPTS
===========================================

WITH MIDJOURNEY:
1. Copy entire prompt from txt file
2. Use command: /imagine [paste prompt]
3. Select best variation
4. Upscale to final resolution
5. Download and optimize for web

WITH DALL-E 3:
1. Copy entire prompt from txt file
2. Paste into ChatGPT with DALL-E 3 access
3. May need to simplify/shorten if too long
4. Generate and download
5. May need multiple iterations

WITH STABLE DIFFUSION:
1. Use prompt as base
2. Add model-specific parameters
3. Adjust for your specific SD model
4. Generate multiple variations
5. Select and upscale best results

WITH LEONARDO.AI / IDEOGRAM:
1. Copy prompt
2. Select appropriate model (prefer 3D/isometric models)
3. Set dimensions as specified in prompt
4. Generate variations
5. Download best results

===========================================
IMAGE PLACEMENT IN LANDING PAGE
===========================================

FILE REFERENCES (for index3.html):

Hero Section:
- Use: batch2-hero-illustration.png
- Location: <img src="./images/batch2-hero-illustration.png">

Feature Section 1:
- Use: batch2-feature-zero-privilege-graphic.png
- Location: Feature row 1, right column

Feature Section 2:
- Use: batch2-feature-ai-detection-graphic.png
- Location: Feature row 2, left column (reverse layout)

Feature Section 3:
- Use: batch2-feature-audit-trail-graphic.png
- Location: Feature row 3, right column

Comparison Section:
- Use: batch2-comparison-illustration.png
- Location: Above or beside comparison table

Architecture Section:
- Use: batch2-architecture-illustration.png
- Location: Replace text-based architecture diagram OR as hero

Testimonial/FAQ Section:
- Use: batch2-testimonial-illustration.png
- Location: Beside testimonials or in FAQ section

Problem Section Icons:
- batch2-icon-standing-privileges.png (Problem 1)
- batch2-icon-credential-sprawl.png (Problem 2)
- batch2-icon-zero-visibility.png (Problem 3)

Benefits Section Icons:
- batch2-icon-shield.png (Benefit 1)
- batch2-icon-clock-speed.png (Benefit 2)
- batch2-icon-compliance.png (Benefit 3)
- batch2-icon-affordable.png (Benefit 4)

How It Works Icons:
- batch2-icon-step1-request.png
- batch2-icon-step2-approval.png
- batch2-icon-step3-credentials.png
- batch2-icon-step4-monitor.png

Checkmark Icon:
- batch2-icon-checkmark.png (used in feature lists)

===========================================
IMAGE OPTIMIZATION
===========================================

After generating images:

1. RESIZE/OPTIMIZE:
   ```bash
   # Install imagemin
   npm install -g imagemin-cli imagemin-pngquant

   # Optimize all images
   imagemin batch2-*.png --out-dir=optimized --plugin=pngquant
   ```

2. CONVERT TO WEBP (optional for better performance):
   ```bash
   # Install cwebp
   brew install webp  # macOS
   # or apt-get install webp  # Linux

   # Convert
   cwebp -q 80 batch2-hero-illustration.png -o batch2-hero-illustration.webp
   ```

3. CREATE RESPONSIVE VERSIONS:
   - Desktop: Full size (as generated)
   - Tablet: 75% of original
   - Mobile: 50% of original

===========================================
QUALITY CHECKLIST
===========================================

Before using generated images, verify:

✓ No readable text appears in images
✓ Colors match ArgusPAM brand palette
✓ Style is consistent across all images
✓ Characters appear diverse and inclusive
✓ Images are friendly and approachable (not intimidating)
✓ Resolution is high enough for retina displays
✓ File size is optimized for web (<500KB per image ideally)
✓ Backgrounds are transparent OR match landing page background
✓ All icons are same style and visual weight
✓ Images work well together as a cohesive set

===========================================
ALTERNATIVE APPROACHES
===========================================

If AI generation doesn't meet needs:

1. HIRE DESIGNER:
   - Use these prompts as creative brief
   - Platforms: Dribbble, Behance, Upwork, Fiverr
   - Budget: $500-2000 for full set

2. USE ILLUSTRATION LIBRARIES:
   - Humaaans (customizable characters)
   - Blush Design (mix and match illustrations)
   - Illustrations.co
   - unDraw (though more flat style)
   - Customize to match brand colors

3. 3D DESIGN TOOLS:
   - Blender (free, powerful)
   - Cinema 4D
   - Spline (web-based 3D design)
   - Create custom 3D illustrations matching prompts

4. HYBRID APPROACH:
   - AI generate base images
   - Designer refines and polishes
   - Best of both worlds

===========================================
CONTACT & SUPPORT
===========================================

For questions about these prompts or the landing page:
- GitHub: https://github.com/lunzai/arguspam
- Issues: https://github.com/lunzai/arguspam/issues

===========================================
VERSION HISTORY
===========================================

Batch 2 - October 5, 2025
- Created 7 main illustration prompts
- Created comprehensive icon prompt set (12 icons)
- Style: Friendly 3D isometric (Soleland-inspired)
- Focus: Graphics/illustrations instead of UI mockups

Batch 1 - October 5, 2025
- Created 4 UI mockup prompts
- Style: Dashboard/interface screenshots
- Focus: Realistic product UI

===========================================

Total Prompts Created: 8 files
Total Images Needed: 19 (7 illustrations + 12 icons)
Style: Consistent friendly 3D isometric
Ready for: Midjourney, DALL-E 3, Stable Diffusion, Leonardo.AI
