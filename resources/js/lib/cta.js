/**
 * One handler for every "ল্যান্ডিং পেজ নিন" action.
 * The onboarding flow lives at /start; when the real purchase flow exists it
 * only needs changing here.
 */
import router from '../router';

export function openCta() {
    router.push({ name: 'start' });
}
