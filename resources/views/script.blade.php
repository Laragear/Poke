@once
    <script>
        // start-poke-script
        if (window.Poke === undefined) {
            window.Poke = {
                started: false,
                route: '{{ $route }}',
                lastPoke: undefined,
                intervalId: undefined,
                interval: {{ $interval }},
                retries: {{ $times }},
                retriesLeft: undefined,
                lifetime: {{ $lifetime }},
                isListeningOnLivewire: false,
                listenLivewire: () => {
                    if (window.Livewire) {
                        console.info('Livewire is not available');

                        return
                    }

                    // If we already registered the listener on Livewire, don't do it again.
                    if (this.isListeningOnLivewire) {
                        return
                    }

                    window.Livewire.on('poke:check', this.renew)
                },
                stop: () => {
                    window.removeEventListener('online', this.expireCheck, false)
                    document.removeEventListener('visibilitychange', this.expireCheckWhenNotHidden, false)
                    document.removeEventListener('livewire:init', this.listenLivewire, false)
                    this.intervalId !== undefined && clearInterval(this.intervalId) && (this.intervalId = undefined)
                    this.retriesLeft = this.retries
                },
                start: () => {
                    if (this.started) {
                        throw new Error('Poke script already started')
                    }

                    this.retriesLeft = this.retries

                    this.lastPoke = new Date()

                    this.intervalId = setInterval(this.renew, this.interval)

                    this.started = true
                },
                startIfNotStarted: () => {
                    if (this.started) {
                        return
                    }

                    window.addEventListener('online', this.expireCheck, false)
                    document.addEventListener('visibilitychange', this.expireCheckWhenNotHidden, false)
                    document.addEventListener('livewire:init', this.listenLivewire, false)

                    this.start()
                },
                expireCheckWhenNotHidden: () => document.visibilityState !== 'hidden' && this.expireCheck(),
                expireCheck: () => {
                    if (navigator.onLine && new Date() - this.lastPoke >= this.interval + this.lifetime) {
                        window.location.reload()
                    }
                },
                renew: async () => {
                    await fetch(this.route, {
                        method: 'HEAD',
                        cache: 'no-cache',
                        redirect: 'error',
                    }).then(data => {
                        if ([204, 200].includes(data.status)) {
                            this.lastPoke = new Date()
                        }

                        this.expireCheck()
                    }).catch(error => {
                        (--this.retriesLeft) < 1 && this.stop()

                        console.error('Error while poking: ', error)
                    });
                },
            }
        }

        window.Poke.startIfNotStarted()
        // end-poke-script
    </script>
@endonce
