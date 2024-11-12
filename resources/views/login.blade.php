<style>
    body {
        font-family: "Arial", sans-serif;
        margin: 0;
        background: #9867ec;
        /* Warna orange cerah */
        color: #333;
    }

    .container {
        width: 100%;
        max-width: 400px;
        height: 90vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .card {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
    }

    h2 {
        text-align: center;
        color: #333;
    }

    form {
        display: flex;
        flex-direction: column;
    }

    input {
        padding: 10px;
        margin-bottom: 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        transition: border-color 0.3s ease-in-out;
        outline: none;
        color: #333;
    }

    input:focus {
        border-color: #673AB7;
        /* Warna orange saat focus */
    }

    button {
        background-color: #673AB7;
        /* Warna orange cerah */
        color: #fff;
        padding: 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease-in-out;
    }

    button:hover {
        background-color: #673AB7;
        /* Warna orange sedikit lebih gelap saat hover */
    }
</style>
<div class="container">
    <div class="card">
        <h2>Login</h2>
        <form method="POST" action="{{url('login/submit')}}">
            <input type="email" id="email" name="email" placeholder="Email" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</div>
