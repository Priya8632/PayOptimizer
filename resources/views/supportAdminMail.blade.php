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
                <p>Dear Admin, </p>
                <p>Greetings from {{ env('APP_NAME') }}!</p>
                <p>The New Support reference is <b>{{ '#' . $support->id }}</b> and their Details:</p>
                <span>Name : <b>{{ $support->name }}</b></span><br>
                <span>Store Name: <b>{{ $user->name }}</b></span><br>
                <span>Email : <b>{{ $support->email }}</b></span><br>
                <span>Message : <b>{{ $support->message }}</b></span>
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
