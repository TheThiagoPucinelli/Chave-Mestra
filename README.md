# 🔑 **Chave Mestra | Master Key**  

![Banner Chave Mestra](https://github.com/user-attachments/assets/88589ee6-2294-4c62-b5c0-e7062e7c5f65)

<p align="center">
  <img src="https://img.shields.io/badge/Status-Projeto%20Pronto-green?style=for-the-badge">
  <img src="https://img.shields.io/badge/Linguagem-PHP-blue?style=for-the-badge">
  <img src="https://img.shields.io/badge/Banco%20de%20Dados-MySQL-orange?style=for-the-badge">
  <img src="https://img.shields.io/badge/Framework-TailwindCSS-06B6D4?style=for-the-badge">
  <img src="https://img.shields.io/badge/Licença-Proprietária-red?style=for-the-badge">
</p>

> Sistema Web desenvolvido para o **gerenciamento digital de chaves físicas** do  
> **Instituto Federal Sul-Rio-Grandense – Campus Pelotas Visconde da Graça (IFSUL CAVG)**,  
> substituindo o controle manual por uma solução **segura, rastreável e automatizada**.  

---

## 📘 Documentação Oficial | Official Documentation  
[Versão Completa do TCC (em desenvolvimento)]([https://docs.google.com/document/d/1Nmj_qny7QZUFK0y7AC45PeGdm0x9l3htqghiiyUTC8Y/edit?usp=sharing](https://docs.google.com/document/d/1rerUFMhpeYbTao0pfDUAvA5MtV_Lc7n7PMwxQWIdLcY/edit?usp=sharing))  

---

## 🚀 Guia de Utilização | User Guide

**Pré-requisito:** importar o banco de dados `chave-mestra3.sql` antes da execução.  

1. **Acesso Inicial**  
   http://127.0.0.1/Chave-Mestra-Version1.8/PHP/login.php  

2. **Cadastro de Usuário**  
   - Informe **Nome, CPF, E-mail e Senha (12–30 caracteres)**  
   - Perfis são atribuídos conforme política interna (Aluno, Servidor, Administrador, Gerente)  

3. **Login**  
   - Acesso via **CPF ou E-mail + Senha**  

4. **Cadastro de Chaves**  
   - Administradores e Gerentes podem criar **número, nome, descrição, quantidade e local**  

5. **Consulta e Busca**  
   - Listagem de todas as chaves com **status em tempo real** (disponível, reservada, emprestada)  

6. **Agendamento de Chave**  
   - Defina **data, hora de início e término**
   - Conflitos são bloqueados automaticamente  
   - Usuários comuns podem agendar até **2 chaves por dia**  

7. **Retirada e Devolução**  
   - Registradas presencialmente por administradores  
   - Com marcação de **data e hora**  

8. **Contato**  
   - Formulário integrado de comunicação com o setor responsável  

---

## 📖 Sobre o Projeto | About the Project  

### Português  
O **Chave Mestra** foi concebido como uma ferramenta institucional para promover a **informatização do controle de chaves físicas**, eliminando planilhas manuais e reduzindo riscos de extravio.  
A solução oferece **rastreabilidade completa**, histórico individual, auditoria e registros automáticos.  

### English  
**Master Key** is a web system designed to **digitally manage physical keys** for rooms and laboratories at IFSUL CAVG.  
It increases **traceability, control, and security**, replacing spreadsheets and handwritten forms with a **modern and auditable platform**.  

---

## 🎯 Objetivos | Objectives

| Português | English |
|------------|----------|
| Desenvolver um sistema web responsivo para informatizar o controle de chaves físicas. | Develop a responsive web system to digitize the management of physical keys. |

### Objetivos Específicos | Specific Objectives  
- Criar uma interface **intuitiva e responsiva** para diferentes dispositivos  
- Otimizar o **cadastro, reserva, retirada e devolução**  
- Implementar **segurança institucional e rastreabilidade**  
- Permitir **histórico individual e global por chave**  
- Registrar **logs e auditorias** para investigações internas  
- Notificar administradores e gerentes sobre movimentações  

---

## ⚙️ Funcionalidades | Key Features

- Cadastro, edição e exclusão de chaves  
- Agendamento com bloqueio automático de conflitos  
- Registro de retirada e devolução com carimbo de tempo  
- Controle de permissões por perfil (Usuário, ADM, Gerente)  
- Histórico individual para cada usuário  
- Histórico geral por chave  
- Painel administrativo com filtros avançados  
- Notificações internas sobre reservas e atrasos  
- Logs de auditoria de todas as ações sensíveis  
- Criptografia de senhas e validações avançadas  

---

## 🧩 Tecnologias | Technologies

| Tecnologia | Finalidade |
|-------------|------------|
| PHP | Back-end e validações |
| MySQL | Banco relacional com integridade |
| HTML5 / CSS3 / TailwindCSS | UI responsiva e moderna |
| JavaScript | Dinamismo e interatividade |
| Scrum (Ágil) | Desenvolvimento iterativo |

---

## 🧱 Estrutura do Banco de Dados | Database Model

| Entidade | Função |
|-----------|--------|
| `usuario` | Armazena dados de alunos, servidores e acessos administrativos |
| `chave` | Identificação, status, localização e quantidade |
| `emprestimo` | Registra retirada e devolução presencial |
| `reserva` | Controla agendamentos por data e hora |
| `notificacao_usuario` | Notifica usuários das movimentações |
| `notificacoes_admin` | Alertas operacionais para gestores |

---

## 📊 Relatórios e Auditoria | Reports & Logs

- Histórico exportável de movimentações  
- Controle por usuário, chave ou período  
- Registro de atrasos e não comparecimentos  
- Logs internos por responsável e ação  
- Auditoria para investigações acadêmicas  
- Controle de recorrência por ambiente  

---

## 🧪 Testes | Testing

- Avaliação com **usuários reais do CAVG**  
- Entrevistas qualitativas com setor de chaves  
- Questionário institucional  
- Ajustes de UX baseados em feedback  
- Testes de conflitos e segurança  

---

## 📅 Cronograma de Desenvolvimento | Development Timeline

| Mês | Etapa Principal |
|------|----------------|
| Abril | Escolha do tema e análise de contexto |
| Maio | Prototipação e documentação |
| Junho | Implementação inicial |
| Julho | Desenvolvimento de funcionalidades complexas |
| Agosto | Testes funcionais internos |
| Setembro | Testes externos, entrevistas e refinamentos |
| Outubro | Otimizações e ajustes de usabilidade |
| Novembro | Validações finais e auditorias |
| Dezembro | Defesa do TCC |

---

## 👥 Autores | Authors

- **Thiago Pucinelli Aires da Silva**  
- **Leonardo Oliveira Garcia**  
- **Orientador:** Prof. Dr. João Ladislau Barbará Lopes  
- **Instituição:** Instituto Federal Sul-Rio-Grandense – CAVG  
- **Conclusão:** *Em desenvolvimento (2025)*  

---

## ⚖️ Licença e Direitos Autorais | License & Copyright

© 2025 Thiago Pucinelli & Leonardo Oliveira  
Todos os direitos reservados. | All rights reserved.  

Repositório disponibilizado **exclusivamente para consulta técnica**.  
Qualquer reprodução, distribuição ou uso indevido constitui infração à  
**Lei nº 9.610/1998 (Brasil)**.  

---

## ✉️ Contato | Contact

- **E-mail:** thiagopucinellisenac@gmail.com  
- [LinkedIn – Thiago Pucinelli](https://www.linkedin.com/in/thiagopucinelli)
