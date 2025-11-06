// Alternar abas
document.querySelectorAll('.tab-button').forEach((button, index) => {
    button.addEventListener('click', () => {
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
        button.classList.add('active');
        document.querySelectorAll('.tab-content')[index].style.display = 'block';
    });
});

// Cancelar solicitação
document.querySelectorAll('.btn-cancel').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        if (confirm('Deseja realmente cancelar esta solicitação?')) {
            fetch('cancelar_solicitacao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            });
        }
    });
});

// Confirmar recebimento
document.querySelectorAll('.confirmar-recebimento').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        if (confirm('Confirmar que você recebeu o item?')) {
            fetch('confirmar_recebimento.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                location.reload();
            });
        }
    });
});
// Menu Sanduiche

document.addEventListener('DOMContentLoaded', function() {
    // Seleciona o botão de alternância (o ícone sanduíche)
    const menuToggle = document.querySelector('.menu-toggle');
    // Seleciona o menu de navegação
    const mainNav = document.querySelector('.main-nav');

    // Adiciona o evento de clique no botão
    menuToggle.addEventListener('click', function() {
        // Alterna (adiciona/remove) a classe 'active' no menu e no botão
        mainNav.classList.toggle('active');
        menuToggle.classList.toggle('active');
        
        // Acessibilidade: Atualiza o estado para leitores de tela
        const isExpanded = menuToggle.classList.contains('active');
        menuToggle.setAttribute('aria-expanded', isExpanded);
        menuToggle.setAttribute('aria-label', isExpanded ? 'Fechar menu principal' : 'Abrir menu principal');
    });

    // Opcional: Fechar o menu automaticamente ao clicar em um link (para mobile)
    const navLinks = document.querySelectorAll('.main-nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (mainNav.classList.contains('active')) {
                mainNav.classList.remove('active');
                menuToggle.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
                menuToggle.setAttribute('aria-label', 'Abrir menu principal');
            }
        });
    });
});