
const cpfToggle = document.getElementById('cpfToggle');
const cnpjToggle = document.getElementById('cnpjToggle');
const cpfGroup = document.getElementById('cpfGroup');
const cnpjGroup = document.getElementById('cnpjGroup');
const cpfInput = document.getElementById('cpf');
const cnpjInput = document.getElementById('cnpj');

cpfToggle.addEventListener('click', () => {
    cpfToggle.classList.add('active');
    cnpjToggle.classList.remove('active');
    cpfGroup.classList.remove('hidden');
    cnpjGroup.classList.add('hidden');
    cnpjInput.value = '';
    cnpjInput.required = false;
    cpfInput.required = false;
});

cnpjToggle.addEventListener('click', () => {
    cnpjToggle.classList.add('active');
    cpfToggle.classList.remove('active');
    cnpjGroup.classList.remove('hidden');
    cpfGroup.classList.add('hidden');
    cpfInput.value = '';
    cpfInput.required = false;
    cnpjInput.required = false;
});


const telefoneInput = document.getElementById('telefone');
telefoneInput.addEventListener('input', (e) => {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        if (value.length <= 10) {
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
        } else {
            value = value.replace(/(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
        }
        e.target.value = value;
    }
});


cpfInput.addEventListener('input', (e) => {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 11) {
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        e.target.value = value;
    }
});

cnpjInput.addEventListener('input', (e) => {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 14) {
        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
        e.target.value = value;
    }
});


const cadastroForm = document.getElementById('cadastroForm');
const errorMessage = document.getElementById('errorMessage');
const successMessage = document.getElementById('successMessage');

cadastroForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    console.log('🔵 Formulário submetido!');
    
    
    errorMessage.style.display = 'none';
    successMessage.style.display = 'none';

    
    const senha = document.getElementById('senha').value;
    const confirmarSenha = document.getElementById('confirmarSenha').value;

    console.log('🔵 Validando senhas...');

    if (senha !== confirmarSenha) {
        console.log('❌ Senhas não coincidem');
        errorMessage.textContent = 'As senhas não coincidem!';
        errorMessage.style.display = 'block';
        return;
    }

    if (senha.length < 6) {
        console.log('❌ Senha muito curta');
        errorMessage.textContent = 'A senha deve ter no mínimo 6 caracteres!';
        errorMessage.style.display = 'block';
        return;
    }

    console.log('✅ Senhas validadas');

    
    const formData = new FormData(cadastroForm);

    
    console.log('📤 Dados sendo enviados:');
    for (let pair of formData.entries()) {
        console.log(`  ${pair[0]}: ${pair[1]}`);
    }

    
    const btnSubmit = cadastroForm.querySelector('.btn-cadastro');
    const btnTextOriginal = btnSubmit.textContent;
    btnSubmit.textContent = 'Cadastrando...';
    btnSubmit.disabled = true;

    try {
        console.log('🔵 Enviando requisição para: includes/processar_cadastro.php');
        
        const response = await fetch('includes/processar_cadastro.php', {
            method: 'POST',
            body: formData
        });

        console.log('📥 Resposta recebida!');
        console.log('  Status:', response.status);
        console.log('  Status Text:', response.statusText);
        console.log('  Headers:', response.headers);

        
        const textResponse = await response.text();
        console.log('📄 Resposta em texto:', textResponse);

        
        let result;
        try {
            result = JSON.parse(textResponse);
            console.log('✅ JSON parseado com sucesso:', result);
        } catch (parseError) {
            console.error('❌ Erro ao parsear JSON:', parseError);
            console.error('Resposta recebida não é JSON válido:', textResponse);
            
            errorMessage.textContent = 'Erro no servidor. Verifique o console para mais detalhes.';
            errorMessage.style.display = 'block';
            
            btnSubmit.textContent = btnTextOriginal;
            btnSubmit.disabled = false;
            return;
        }

        if (result.success) {
            console.log('✅ Cadastro realizado com sucesso!');
            successMessage.textContent = result.message;
            successMessage.style.display = 'block';
            
            
            cadastroForm.reset();
            
            
            setTimeout(() => {
                console.log('🔄 Redirecionando para login...');
                window.location.href = 'login.php';
            }, 2000);
        } else {
            console.log('❌ Erro retornado pelo servidor:', result.message);
            errorMessage.textContent = result.message;
            errorMessage.style.display = 'block';
            
            
            btnSubmit.textContent = btnTextOriginal;
            btnSubmit.disabled = false;
        }
    } catch (error) {
        console.error('❌ ERRO NO FETCH:', error);
        console.error('Detalhes do erro:', {
            name: error.name,
            message: error.message,
            stack: error.stack
        });
        
        errorMessage.textContent = 'Erro ao processar cadastro. Verifique o console (F12) para mais detalhes.';
        errorMessage.style.display = 'block';
        
        
        btnSubmit.textContent = btnTextOriginal;
        btnSubmit.disabled = false;
    }
});