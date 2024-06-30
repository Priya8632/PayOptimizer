@include('layouts.header')

<div class="container h-100">
    <section>
        <div class="row">
            <div class="header">
                <h6>{{ env('APP_NAME') }} Support</h6>
                <h2>{{ env('APP_NAME') }} App</h2>
            </div>
        </div>
        <div class="row">
            <div class="emailDesign">
                <p>Dear Customer, </p>
                <p>Greetings from {{ env('APP_NAME') }}!</p>
                <p>Your request is {{ $support->message }} We will keep you updated</p>
                <p><b>
                        Best Regards,<br>
                        Customer Success Team
                </p>
            </div>
        </div>
    </section>
</div>

</body>

</html>
