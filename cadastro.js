// cadastro.js

document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector(".cadastro-form");
    const cpfCnpjInput = document.getElementById("cpf_cnpj");
    const telefoneInput = document.getElementById("telefone");
    const senhaInput = document.getElementById("senha");
    const confirmaSenhaInput = document.getElementById("confirma-senha");

    // --- Máscara para CPF ou CNPJ ---
    cpfCnpjInput.addEventListener("input", () => {
        let value = cpfCnpjInput.value.replace(/\D/g, "");

        if (value.length <= 11) {
            // CPF: 000.000.000-00
            value = value.replace(/(\d{3})(\d)/, "$1.$2");
            value = value.replace(/(\d{3})(\d)/, "$1.$2");
            value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        } else {
            // CNPJ: 00.000.000/0000-00
            value = value.replace(/^(\d{2})(\d)/, "$1.$2");
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
            value = value.replace(/\.(\d{3})(\d)/, ".$1/$2");
            value = value.replace(/(\d{4})(\d)/, "$1-$2");
        }

        cpfCnpjInput.value = value;
    });

    // --- Máscara para telefone ---
    telefoneInput.addEventListener("input", () => {
        let value = telefoneInput.value.replace(/\D/g, "");
        if (value.length > 11) value = value.slice(0, 11);

        if (value.length > 10) {
            telefoneInput.value = value.replace(
                /^(\d{2})(\d{5})(\d{4})$/,
                "($1) $2-$3"
            );
        } else if (value.length > 5) {
            telefoneInput.value = value.replace(
                /^(\d{2})(\d{4})(\d{0,4})$/,
                "($1) $2-$3"
            );
        } else if (value.length > 2) {
            telefoneInput.value = value.replace(/^(\d{2})(\d{0,5})$/, "($1) $2");
        } else {
            telefoneInput.value = value.replace(/^(\d*)$/, "($1");
        }
    });

    // --- Validação antes de enviar ---
    form.addEventListener("submit", (e) => {
        const senha = senhaInput.value.trim();
        const confirmaSenha = confirmaSenhaInput.value.trim();
        const email = document.getElementById("email").value.trim();

        // Senhas iguais
        if (senha !== confirmaSenha) {
            e.preventDefault();
            alert("As senhas não coincidem. Verifique e tente novamente.");
            confirmaSenhaInput.focus();
            return;
        }

        // Validação simples de e-mail
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert("Por favor, insira um e-mail válido.");
            document.getElementById("email").focus();
            return;
        }

        // Confere se o tipo de perfil foi selecionado
        const perfilSelecionado = document.querySelector('input[name="perfil"]:checked');
        if (!perfilSelecionado) {
            e.preventDefault();
            alert("Selecione um tipo de cadastro (Doador, Beneficiário ou ONG).");
            return;
        }

        // Tudo certo
        console.log("Formulário validado com sucesso!");
    });
});
