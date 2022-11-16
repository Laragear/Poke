@once
    <script>
        // start-poke-script
        if (typeof poke_renew === 'undefined') {
            let poke_last = new Date();

            const poke_renew = async () => {
                await fetch('{{ $route }}', {
                    method: 'HEAD',
                    cache: 'no-cache',
                    redirect: 'error',
                }).then(data => {
                    if (data.status === 204) {
                        poke_last = new Date();
                    }

                    poke_expire_check();
                }).catch(error => {
                    console.error('Error while poking: ', error);
                });
            };

            const poke_expire_check = () => {
                if (navigator.onLine && new Date() - poke_last >= {{ $interval }} + {{ $lifetime }}) {
                    window.location.reload();
                }
            };

            setInterval(() => { poke_renew(); }, {{ $interval }} );

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState !== 'hidden') { poke_expire_check(); }
            }, false);

            window.addEventListener('online', poke_expire_check, false);
        }
        // end-poke-script
    </script>
@endonce