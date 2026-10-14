/** Uniform rejection sampling; never map the incomplete remainder by modulo. */
export function randomIndices(size: number, count: number): number[] {
    if (!Number.isSafeInteger(size) || size < 1 || size > 0x100000000
        || !Number.isSafeInteger(count) || count < 0 || count > 10000) {
        throw new RangeError('Invalid random selection bounds')
    }
    const limit = Math.floor(0x100000000 / size) * size
    const result: number[] = []
    while (result.length < count) {
        const samples = new Uint32Array(count - result.length)
        crypto.getRandomValues(samples)
        for (const sample of samples) {
            if (sample < limit) result.push(sample % size)
        }
    }
    return result
}

export function randomPassword(alphabet: string, length: number): string {
    const characters = [...new Set(alphabet)]
    return characters.length ? randomIndices(characters.length, length).map(i => characters[i]).join('') : ''
}
