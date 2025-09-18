# Bricks AI Page Builder

Create Bricks Builder websites with AI-generated pages, sections, content, and images.

## Features

- Settings under Settings > Bricks AI: Gemini API, Image API, defaults
- Questionnaire Wizard under Pages > Bricks AI Wizard
- Bricks editor button “Generate AI Content” with live preview and apply
- AI text via Gemini-like endpoint; images via configurable API (fallback to Picsum)
- Stores generated layout JSON in post meta key `bricks_data`
- Logging via hidden CPT; Tools > Bricks AI Logs viewer
- Nonce + capability checks on REST; modular classes; translation-ready

## Installation

1. Copy the `bricks-ai-page-builder` folder into `wp-content/plugins/`
2. Activate “Bricks AI Page Builder”
3. Configure under Settings > Bricks AI

## REST API

Namespace: `addweb-bricks-ai/v1`
- POST `/generate/text` { prompt, params? }
- POST `/generate/image` { prompt, size? }
- POST `/pages/create` { primaryColor, businessType, logoColors[], pageTypes[], count }
- POST `/bricks/apply` { postId, data }

## Security

- Requires logged-in users with `edit_posts`
- REST nonce: `X-WP-Nonce` from `wp_create_nonce('wp_rest')`

## Notes

- Adjust AI/Image endpoints to your provider schemas
- Wizard logo color detection is approximate client-side quantization

License: GPL-2.0-or-later
