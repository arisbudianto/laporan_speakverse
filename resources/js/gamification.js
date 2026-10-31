const rewardQueue = [];
let rewardOpen = false;

/**
 * Membuat seluruh CSS popup secara otomatis.
 *
 * Dengan cara ini kita tidak perlu menambahkan CSS terpisah
 * ke app.css hanya untuk popup gamification.
 */
function ensureRewardStyles() {
    if (document.getElementById('sv-gamification-styles')) {
        return;
    }

    const style = document.createElement('style');

    style.id = 'sv-gamification-styles';

    style.textContent = `
        @keyframes svRewardIn {
            from {
                opacity: 0;
                transform: translateY(18px) scale(.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes svRewardPop {
            0% {
                transform: scale(.82);
            }

            65% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes svConfettiFall {
            from {
                transform:
                    translate3d(0, -12vh, 0)
                    rotate(0deg);

                opacity: 1;
            }

            to {
                transform:
                    translate3d(
                        var(--sv-x),
                        110vh,
                        0
                    )
                    rotate(720deg);

                opacity: .1;
            }
        }

        #sv-reward-backdrop {
            position: fixed;
            inset: 0;
            z-index: 99990;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 20px;

            background:
                rgba(15, 23, 42, .64);

            backdrop-filter:
                blur(10px);
        }

        #sv-reward-card {
            position: relative;
            z-index: 99992;

            width:
                min(100%, 470px);

            border-radius:
                28px;

            border:
                1px solid
                rgba(148, 163, 184, .26);

            background:
                white;

            color:
                #0f172a;

            box-shadow:
                0 30px 80px
                rgba(15, 23, 42, .28);

            padding:
                28px;

            text-align:
                center;

            animation:
                svRewardIn
                .32s
                ease-out
                both;
        }

        html.dark #sv-reward-card {
            background:
                #111827;

            color:
                #f8fafc;

            border-color:
                rgba(255, 255, 255, .10);
        }

        .sv-reward-icon {
            width:
                76px;

            height:
                76px;

            margin:
                0 auto;

            display:
                grid;

            place-items:
                center;

            border-radius:
                24px;

            background:
                linear-gradient(
                    135deg,
                    #dbeafe,
                    #ede9fe
                );

            font-size:
                40px;

            animation:
                svRewardPop
                .52s
                ease-out
                both;
        }

        html.dark .sv-reward-icon {
            background:
                rgba(59, 130, 246, .14);
        }

        .sv-reward-title {
            margin:
                18px 0 0;

            font-size:
                26px;

            line-height:
                1.15;

            font-weight:
                900;

            letter-spacing:
                -.03em;
        }

        .sv-reward-message {
            margin:
                8px auto 0;

            max-width:
                370px;

            color:
                #64748b;

            font-size:
                14px;

            line-height:
                1.6;
        }

        html.dark .sv-reward-message {
            color:
                #94a3b8;
        }

        .sv-reward-stats {
            display:
                grid;

            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );

            gap:
                10px;

            margin-top:
                20px;
        }

        .sv-reward-stat {
            border:
                1px solid
                #e2e8f0;

            border-radius:
                18px;

            padding:
                13px 12px;

            background:
                #f8fafc;
        }

        html.dark .sv-reward-stat {
            border-color:
                rgba(255, 255, 255, .08);

            background:
                rgba(255, 255, 255, .04);
        }

        .sv-reward-stat-label {
            display:
                block;

            color:
                #64748b;

            font-size:
                11px;

            font-weight:
                800;

            text-transform:
                uppercase;

            letter-spacing:
                .08em;
        }

        html.dark .sv-reward-stat-label {
            color:
                #94a3b8;
        }

        .sv-reward-stat-value {
            display:
                block;

            margin-top:
                4px;

            font-size:
                19px;

            font-weight:
                900;
        }

        .sv-reward-badges {
            margin-top:
                16px;

            display:
                grid;

            gap:
                8px;
        }

        .sv-reward-badge {
            display:
                flex;

            align-items:
                center;

            gap:
                10px;

            text-align:
                left;

            border:
                1px solid
                #fde68a;

            background:
                #fffbeb;

            color:
                #92400e;

            border-radius:
                16px;

            padding:
                11px 12px;
        }

        html.dark .sv-reward-badge {
            border-color:
                rgba(245, 158, 11, .24);

            background:
                rgba(245, 158, 11, .10);

            color:
                #fcd34d;
        }

        .sv-reward-button {
            width:
                100%;

            margin-top:
                20px;

            border:
                0;

            border-radius:
                16px;

            background:
                #2563eb;

            color:
                white;

            padding:
                13px 16px;

            font-size:
                14px;

            font-weight:
                900;

            cursor:
                pointer;

            transition:
                background-color .2s ease,
                transform .2s ease;
        }

        .sv-reward-button:hover {
            background:
                #1d4ed8;
        }

        .sv-reward-button:active {
            transform:
                scale(.98);
        }

        .sv-confetti-piece {
            position:
                fixed;

            z-index:
                99991;

            top:
                -30px;

            width:
                9px;

            height:
                16px;

            border-radius:
                2px;

            pointer-events:
                none;

            animation:
                svConfettiFall
                var(--sv-duration)
                linear
                forwards;
        }

        @media (max-width: 520px) {
            #sv-reward-backdrop {
                padding:
                    16px;
            }

            #sv-reward-card {
                border-radius:
                    24px;

                padding:
                    24px 18px;
            }

            .sv-reward-icon {
                width:
                    68px;

                height:
                    68px;

                border-radius:
                    21px;

                font-size:
                    36px;
            }

            .sv-reward-title {
                font-size:
                    23px;
            }

            .sv-reward-stats {
                gap:
                    8px;
            }

            .sv-reward-stat {
                padding:
                    12px 8px;
            }

            .sv-reward-stat-value {
                font-size:
                    16px;
            }
        }

        @media (
            prefers-reduced-motion:
            reduce
        ) {
            #sv-reward-card,
            .sv-reward-icon,
            .sv-confetti-piece {
                animation:
                    none !important;
            }
        }
    `;

    document.head.appendChild(
        style
    );
}


/**
 * Membuat efek confetti.
 *
 * Hanya dipakai untuk:
 *
 * - Unit Completed
 * - Level Up
 * - Badge baru
 */
function createConfetti() {
    const colors = [
        '#2563eb',
        '#7c3aed',
        '#f59e0b',
        '#ef4444',
        '#10b981',
        '#06b6d4',
    ];

    for (
        let index = 0;
        index < 44;
        index += 1
    ) {
        const piece =
            document.createElement(
                'span'
            );

        piece.className =
            'sv-confetti-piece';

        piece.style.left =
            `${Math.random() * 100}vw`;

        piece.style.background =
            colors[
                Math.floor(
                    Math.random()
                    *
                    colors.length
                )
            ];

        piece.style.setProperty(
            '--sv-duration',
            `${
                2.8
                +
                Math.random()
                *
                2.2
            }s`
        );

        piece.style.setProperty(
            '--sv-x',
            `${
                -120
                +
                Math.random()
                *
                240
            }px`
        );

        piece.style.animationDelay =
            `${
                Math.random()
                *
                0.35
            }s`;

        document.body.appendChild(
            piece
        );

        window.setTimeout(
            () => {
                piece.remove();
            },
            5600
        );
    }
}


/**
 * Menghindari HTML injection ketika data
 * berasal dari response backend.
 */
function escapeHtml(value) {
    return String(
        value ?? ''
    )
        .replaceAll(
            '&',
            '&amp;'
        )
        .replaceAll(
            '<',
            '&lt;'
        )
        .replaceAll(
            '>',
            '&gt;'
        )
        .replaceAll(
            '"',
            '&quot;'
        )
        .replaceAll(
            "'",
            '&#039;'
        );
}


/**
 * Menampilkan satu reward popup.
 */
function renderReward(reward) {
    ensureRewardStyles();

    rewardOpen =
        true;

    const backdrop =
        document.createElement(
            'div'
        );

    backdrop.id =
        'sv-reward-backdrop';

    /*
    |--------------------------------------------------------------------------
    | Badge
    |--------------------------------------------------------------------------
    */

    const badges =
        Array.isArray(
            reward.badges
        )
            ? reward.badges
            : [];

    /*
    |--------------------------------------------------------------------------
    | Icon utama
    |--------------------------------------------------------------------------
    */

    const icon =
        reward.unit_completed
            ? '🏆'
            : reward.level_up
                ? '✨'
                : badges.length > 0
                    ? '🏅'
                    : '🎉';

    /*
    |--------------------------------------------------------------------------
    | Badge HTML
    |--------------------------------------------------------------------------
    */

    const badgeHtml =
        badges
            .map(
                (badge) => `
                    <div class="sv-reward-badge">

                        <span
                            style="
                                font-size:
                                24px;
                            "
                        >
                            ${
                                escapeHtml(
                                    badge.icon
                                    ||
                                    '🏅'
                                )
                            }
                        </span>

                        <div>

                            <div
                                style="
                                    font-size:
                                        12px;

                                    font-weight:
                                        900;

                                    text-transform:
                                        uppercase;

                                    letter-spacing:
                                        .07em;
                                "
                            >
                                New badge
                            </div>

                            <div
                                style="
                                    margin-top:
                                        2px;

                                    font-size:
                                        14px;

                                    font-weight:
                                        900;
                                "
                            >
                                ${
                                    escapeHtml(
                                        badge.name
                                    )
                                }
                            </div>

                        </div>

                    </div>
                `
            )
            .join('');


    /*
    |--------------------------------------------------------------------------
    | Isi popup
    |--------------------------------------------------------------------------
    */

    backdrop.innerHTML = `
        <div
            id="sv-reward-card"
            role="dialog"
            aria-modal="true"
            aria-labelledby="sv-reward-title"
        >

            <div class="sv-reward-icon">
                ${icon}
            </div>

            <h2
                id="sv-reward-title"
                class="sv-reward-title"
            >
                ${
                    escapeHtml(
                        reward.title
                        ||
                        'Great Job!'
                    )
                }
            </h2>

            <p class="sv-reward-message">
                ${
                    escapeHtml(
                        reward.message
                        ||
                        'Your progress has been saved.'
                    )
                }
            </p>


            <div class="sv-reward-stats">

                <div class="sv-reward-stat">

                    <span
                        class="sv-reward-stat-label"
                    >
                        XP earned
                    </span>

                    <span
                        class="sv-reward-stat-value"
                    >
                        +${
                            Number(
                                reward.xp_earned
                                ||
                                0
                            )
                        }
                        XP
                    </span>

                </div>


                <div class="sv-reward-stat">

                    <span
                        class="sv-reward-stat-label"
                    >
                        SpeakCoins
                    </span>

                    <span
                        class="sv-reward-stat-value"
                    >
                        +${
                            Number(
                                reward.coins_earned
                                ||
                                0
                            )
                        }
                        🪙
                    </span>

                </div>

            </div>


            ${
                reward.unit_completed
                    ? `
                        <div
                            class="sv-reward-badge"
                            style="
                                margin-top:
                                    16px;
                            "
                        >
                            <span
                                style="
                                    font-size:
                                        24px;
                                "
                            >
                                🏆
                            </span>

                            <div>

                                <div
                                    style="
                                        font-size:
                                            12px;

                                        font-weight:
                                            900;

                                        text-transform:
                                            uppercase;

                                        letter-spacing:
                                            .07em;
                                    "
                                >
                                    Unit completed
                                </div>

                                <div
                                    style="
                                        margin-top:
                                            2px;

                                        font-size:
                                            14px;

                                        font-weight:
                                            900;
                                    "
                                >
                                    ${
                                        escapeHtml(
                                            reward.unit
                                                ?.title
                                            ||
                                            'Learning Unit'
                                        )
                                    }
                                </div>

                            </div>

                        </div>
                    `
                    : ''
            }


            ${
                reward.level_up
                    ? `
                        <div
                            class="sv-reward-badge"
                            style="
                                margin-top:
                                    16px;
                            "
                        >

                            <span
                                style="
                                    font-size:
                                        24px;
                                "
                            >
                                📈
                            </span>

                            <div>

                                <div
                                    style="
                                        font-size:
                                            12px;

                                        font-weight:
                                            900;

                                        text-transform:
                                            uppercase;

                                        letter-spacing:
                                            .07em;
                                    "
                                >
                                    Level up
                                </div>

                                <div
                                    style="
                                        margin-top:
                                            2px;

                                        font-size:
                                            14px;

                                        font-weight:
                                            900;
                                    "
                                >
                                    Level
                                    ${
                                        Number(
                                            reward.level_before
                                            ||
                                            1
                                        )
                                    }

                                    →

                                    Level
                                    ${
                                        Number(
                                            reward.level_after
                                            ||
                                            1
                                        )
                                    }
                                </div>

                            </div>

                        </div>
                    `
                    : ''
            }


            ${
                badges.length > 0
                    ? `
                        <div class="sv-reward-badges">
                            ${badgeHtml}
                        </div>
                    `
                    : ''
            }


            <button
                type="button"
                class="sv-reward-button"
            >
                Continue Learning
            </button>

        </div>
    `;


    /*
    |--------------------------------------------------------------------------
    | Close Handler
    |--------------------------------------------------------------------------
    */

    let closed =
        false;

    const handleEscape =
        (event) => {
            if (
                event.key
                ===
                'Escape'
            ) {
                close();
            }
        };

    const close =
        () => {
            if (closed) {
                return;
            }

            closed =
                true;

            document.removeEventListener(
                'keydown',
                handleEscape
            );

            backdrop.remove();

            rewardOpen =
                false;

            /*
             * Jika ada beberapa reward,
             * tampilkan popup berikutnya.
             */
            if (
                rewardQueue.length
                >
                0
            ) {
                const nextReward =
                    rewardQueue.shift();

                window.setTimeout(
                    () => {
                        renderReward(
                            nextReward
                        );
                    },
                    120
                );
            }
        };


    backdrop
        .querySelector(
            '.sv-reward-button'
        )
        ?.addEventListener(
            'click',
            close
        );


    backdrop.addEventListener(
        'click',
        (event) => {
            /*
             * Klik area gelap di luar card.
             */
            if (
                event.target
                ===
                backdrop
            ) {
                close();
            }
        }
    );


    document.addEventListener(
        'keydown',
        handleEscape
    );


    /*
    |--------------------------------------------------------------------------
    | Masukkan popup ke halaman
    |--------------------------------------------------------------------------
    */

    document.body.appendChild(
        backdrop
    );


    /*
    |--------------------------------------------------------------------------
    | Confetti
    |--------------------------------------------------------------------------
    */

    if (
        reward.unit_completed
        ||
        reward.level_up
        ||
        badges.length > 0
    ) {
        createConfetti();
    }
}


/**
 * Global function.
 *
 * Bisa dipanggil manual:
 *
 * window.showGamificationReward(data);
 */
window.showGamificationReward =
    function (reward) {
        /*
         * Tidak menampilkan popup jika backend
         * menyatakan reward tidak perlu ditampilkan.
         */
        if (
            !reward
            ||
            !reward.show_popup
        ) {
            return;
        }

        /*
         * Jika popup sedang terbuka,
         * simpan reward berikutnya ke queue.
         */
        if (rewardOpen) {
            rewardQueue.push(
                reward
            );

            return;
        }

        renderReward(
            reward
        );
    };


/**
 * --------------------------------------------------------------------------
 * FETCH INTERCEPTOR
 * --------------------------------------------------------------------------
 *
 * Quiz SpeakVerse sebelumnya sudah banyak menggunakan fetch().
 *
 * Agar kita tidak perlu mengedit setiap Blade quiz hanya untuk popup,
 * response JSON otomatis diperiksa di sini.
 *
 * Jika controller mengembalikan:
 *
 * {
 *     gamification: {
 *         show_popup: true,
 *         ...
 *     }
 * }
 *
 * popup akan langsung tampil.
 */

const originalFetch =
    window.fetch.bind(
        window
    );

window.fetch =
    async (...args) => {
        /*
         * Jalankan request asli.
         */
        const response =
            await originalFetch(
                ...args
            );

        try {
            /*
             * Clone response agar response asli
             * tetap bisa dibaca script halaman.
             */
            const cloned =
                response.clone();

            const contentType =
                cloned.headers.get(
                    'content-type'
                )
                ||
                '';

            /*
             * Hanya periksa JSON.
             */
            if (
                contentType.includes(
                    'application/json'
                )
            ) {
                const data =
                    await cloned.json();

                /*
                 * Tampilkan reward popup.
                 */
                if (
                    data
                        ?.gamification
                        ?.show_popup
                ) {
                    window
                        .showGamificationReward(
                            data
                                .gamification
                        );
                }
            }
        } catch (error) {
            /*
             * Error popup tidak boleh merusak
             * proses quiz utama.
             */
            console.debug(
                'SpeakVerse gamification popup skipped.',
                error
            );
        }

        /*
         * Tetap return response asli.
         */
        return response;
    };