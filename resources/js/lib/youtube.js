/**
 * Pulls the video id out of any common YouTube URL shape and exposes a
 * privacy-friendly embed URL. Returns null when nothing usable is configured,
 * so callers can show the placeholder instead of a broken frame.
 */
export function youtubeEmbedUrl(url) {
    if (!url) return null;

    const patterns = [
        /(?:youtube\.com\/watch\?(?:.*&)?v=)([\w-]{11})/,
        /(?:youtu\.be\/)([\w-]{11})/,
        /(?:youtube\.com\/embed\/)([\w-]{11})/,
        /(?:youtube\.com\/shorts\/)([\w-]{11})/,
    ];

    for (const pattern of patterns) {
        const match = url.match(pattern);
        if (match) {
            return `https://www.youtube-nocookie.com/embed/${match[1]}?rel=0&modestbranding=1`;
        }
    }

    return null;
}
