<h2 style="text-align: center;">Registrazione Corrispettivo</h2>
<?php if(isset($content) && $content=="ok"){?>
        <h2 style="text-align: center; color: green;">Registrazione  Avvenuta</h2>
        <script>
            // Reindirizza al form iniziale dopo 5 secondi
            setTimeout(function() {
                window.location.href = window.location.pathname; // Torna alla stessa pagina
            }, 5000);
        </script>
<?php }else{ ?>
    <?php echo"<pre>"; print_r($_SESSION); echo"</pre>"; ?>
    <form method="POST">
        <br>
        <div class="form-group">
            <label for="descrizione">Descrizione</label>
            <input type="text" id="descrizione" name="descrizione" value="Visita guidata" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email">
        </div>

        <div class="form-group">
            <label for="corrispettivo">Valore Corrispettivo</label>
            <input type="text" id="corrispettivo" name="corrispettivo" required>
        </div>
        <input type="submit" style="background-color: #A06AFF;" value="Invia">
    </form>
<?php } ?>