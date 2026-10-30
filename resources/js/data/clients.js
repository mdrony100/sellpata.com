/**
 * Real client sites and real reviews come from the admin panel only. They stay
 * empty until the owner adds genuine entries; nothing is fabricated here.
 */
import config from '../lib/config';

const DB_CONTENT = config.content ?? {};

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
export const clients = DB_CONTENT.clients ?? [];

/**
 * Genuine reviews only. Empty until the client provides them, so the reviews
 * area shows the Bengali empty state instead of fabricated testimonials.
 * Shape: { id, name, business, quote }
 */
export const reviews = DB_CONTENT.reviews ?? [];
