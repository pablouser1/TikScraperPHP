// https://github.com/berstend/puppeteer-extra/blob/master/packages/puppeteer-extra-plugin-stealth/evasions/media.codecs/index.js

(function () {
    utils.init();
    const parseInput = arg => {
        const [mime, codecStr] = arg.trim().split(';')
        let codecs = []
        if (codecStr && codecStr.includes('codecs="')) {
            codecs = codecStr
                .trim()
                .replace(`codecs="`, '')
                .replace(`"`, '')
                .trim()
                .split(',')
                .filter(x => !!x)
                .map(x => x.trim())
        }
        return {
            mime,
            codecStr,
            codecs
        }
    }

    const canPlayType = {
        // Intercept certain requests
        apply: function (target, ctx, args) {
            if (!args || !args.length) {
                return target.apply(ctx, args)
            }
            const { mime, codecs } = parseInput(args[0])
            // This specific mp4 codec is missing in Chromium
            if (mime === 'video/mp4') {
                if (codecs.includes('avc1.42E01E')) {
                    return 'probably'
                }
            }
            // This mimetype is only supported if no codecs are specified
            if (mime === 'audio/x-m4a' && !codecs.length) {
                return 'maybe'
            }

            // This mimetype is only supported if no codecs are specified
            if (mime === 'audio/aac' && !codecs.length) {
                return 'probably'
            }
            // Everything else as usual
            return target.apply(ctx, args)
        }
    }

    /* global HTMLMediaElement */
    utils.replaceWithProxy(
        HTMLMediaElement.prototype,
        'canPlayType',
        canPlayType
    )
})()
