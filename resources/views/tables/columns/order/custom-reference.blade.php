<div>
    <div class="flex ">
        <div class="flex max-w-max" style="">
            <div class="fi-ta-text-item inline-flex items-center gap-1.5  ">

                <span class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  " style="">
                    {{ $getState() }}
                </span>

            </div>
        </div>
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ $getRecord()->name }} | (<a
            href="https://api.whatsapp.com/send?phone={{ $getRecord()->phone }}&text=">{{ $getRecord()->phone }}</a>)
        <br>
        {{ $getRecord()->address }}
    </p>
</div>
