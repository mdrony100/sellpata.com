/**
 * Real client data. Intentionally empty for this phase: nothing here may be
 * invented, so the site shows a Bengali empty state until real entries are
 * supplied.
 *
 * Shape of a real entry:
 * { id, brand, category, image, url }
 * `image` should be a local asset (not a hotlinked or unlicensed file) and
 * `url` must be the live landing page.
 */
export const clients = [];

/**
 * Genuine reviews only. Empty until the client provides them, so the reviews
 * area shows the Bengali empty state instead of fabricated testimonials.
 * Shape: { id, name, business, quote }
 */
export const reviews = [];
