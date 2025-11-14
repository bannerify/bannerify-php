# PHP SDK Playground

Test the Bannerify PHP SDK locally before publishing.

## Setup

1. Make sure you have the API key in `.env`:
   ```bash
   API_KEY=your_api_key_here
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Run the test:
   ```bash
   php playground/test.php
   ```

## What it tests

- ✅ Create image with array modifications
- ✅ Create image with typed Modification objects (type-safe!)
- ✅ Create SVG image
- ✅ Generate signed URL
- ✅ Error handling

Results will be saved in `playground/output/`.

