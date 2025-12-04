Redesign the index.php homepage of a classic PHP website to convey a premium, polished, high-end product identity. Prioritize clean layout structure, confident typography (Rubik + JetBrains Mono), balanced negative space, a dark theme (#1E1E1E as base), and a refined developer-focused tone.
Integrate Barba.js page transitions and deliberate animation pacing so users can visually absorb the motion. Follow these rules and produce layout, UI components, animations, and transition logic:

1. Overall Layout & Styling Requirements

• Use a minimal, luxury-inspired composition with consistent spacing, large breathable sections, and precision alignment.
• Typography hierarchy must feel editorial and confident.
• Avoid boxy shadows and corny gradients; use subtle overlays, slight glass textures, and carefully restrained accent colors.
• Ensure the design feels like a premium developer tool or digital product rather than a template.

2. Components to Include on index.php

Hero Section:
• Large headline, sub-headline, CTA buttons, and a subtle animated background layer.

Navigation Bar:
• Minimal top navbar with bold logo, spaced nav links, and hover states that feel deliberate (underline reveal or micro color shift).

Feature Cards / Sections:
• 3–4 horizontally arranged feature blocks describing the platform or product.
• Include simple geometric icons or line-style illustrations.

Footer:
• Clean, low-profile footer with essential links, copyright, and small accent line.

3. Unique Animations for Each Component (GSAP or Barba.js-compatible)

Hero Section Animation:
• Soft fade-in (200–260ms).
• Headline rises by 2–3% distance with slight easing.
• Background element slow parallax drift.
• CTA buttons enter with micro-stagger (40–60ms).

Navbar Animation:
• Delayed fade-slide from top (80–120ms).
• Each nav link uses subtle underline scale-in on hover (not slide).

Feature Cards Animation:
• Stagger reveal (60–80ms between cards).
• Each card expands from 96% scale to 100% with a clean easeOut curve.
• Text and icon fade upward slightly.

Footer Animation:
• Gentle fade-in from 5% lower position after the cards complete.
• Timing should feel like a closing gesture, not an entrance.

4. Barba.js Page Transition Requirements

Global Transition:
• On link click, fade out the current page to 0 opacity with a 2–4% downward slide.
• Apply a deliberate delay (200–300ms) before injecting/loading the next page so the user perceives the transition.
• When the new page loads, fade it in from 0 to 1 opacity, lifting by 2–3%.
• Use a shared easing curve across all transitions for consistency (easeOutQuad or cubic-bezier equivalent).
• Preload the next page when hovering over links to reduce perceived waiting.

Optional Brand Moment:
• A subtle overlay wipe (solid dark color at 10–15% opacity) that briefly appears during the transition but stays under 140ms so it doesn’t overpower the content.

5. Final Delivery Expectations

Produce:
• The redesigned index.php layout
• The UI structure for all components
• Suggested CSS rules and spacing system
• Detailed animation timeline per component
• Barba.js transition logic with controllable delay
• Notes on maintaining performance (60fps, no heavy blurs, lightweight SVGs)

The final result must feel intentional, luxurious, and engineered with clarity, avoiding generic visuals or template-like patterns.”
