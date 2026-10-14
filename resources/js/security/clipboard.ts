let generation = 0

/** Reading can be denied by the browser. In that case do not overwrite content
 * we cannot identify. Clipboard APIs offer no atomic compare-and-clear. */
export async function copySecret(value: string): Promise<void> {
    await navigator.clipboard.writeText(value)
    const copy = ++generation
    setTimeout(async () => {
        try {
            if (copy === generation && document.hasFocus()
                && await navigator.clipboard.readText() === value && copy === generation) {
                await navigator.clipboard.writeText('')
            }
        } catch {
            // Permission denied or tab inactive: keep user data untouched.
        }
    }, 30000)
}
