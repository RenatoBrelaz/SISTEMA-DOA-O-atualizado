document.querySelectorAll('input[name="enderecoColeta"]').forEach(radio => {
    radio.addEventListener('change', () => {
        const enderecoPrincipal = document.getElementById('endereco-principal');
        const novoEndereco = document.getElementById('novo-endereco-fields');

        if (radio.value === 'novo') {
            novoEndereco.style.display = 'block';
            enderecoPrincipal.style.display = 'none';
        } else {
            novoEndereco.style.display = 'none';
            enderecoPrincipal.style.display = 'block';
        }
    });
});
