# 🔐 Chave Mestra – Sistema de Controle de Chaves

Sistema Web para controle e gerenciamento do uso de chaves físicas no Instituto Federal Sul-Rio-Grandense – Campus Pelotas Visconde da Graça (IFSUL CAVG).  
Desenvolvido como **Trabalho de Conclusão de Curso (TCC)** por **Thiago Pucinelli** e **Leonardo Oliveira**.

---

## 📘 Sobre o Projeto

**Chave Mestra** é uma aplicação web desenvolvida com o propósito de digitalizar e modernizar o processo de controle de chaves de salas e laboratórios da instituição, substituindo o método manual (papel e caneta) por um sistema informatizado, seguro, eficiente e intuitivo.

A ausência de um sistema informatizado gerava dificuldades na rastreabilidade das chaves, extravios, erros manuais e lentidão nos processos. Através desta solução, busca-se proporcionar mais praticidade, segurança, organização e controle.

---

## 🧑‍💻 Autores

- Thiago Pucinelli Aires da Silva  
- Leonardo Oliveira Garcia  
- 📚 **Orientador:** João Ladislau Barbará Lopes  
- 🏫 **Instituição:** Instituto Federal Sul-Rio-Grandense – Campus Pelotas Visconde da Graça (IFSUL CAVG)  
- 📅 **Data de Conclusão:** undefined

---

## 🎯 Objetivos

### Objetivo Geral

Desenvolver um sistema web responsivo que informatize o controle de retirada e devolução de chaves físicas no IFSUL CAVG, substituindo os registros manuais por uma solução digital confiável.

### Objetivos Específicos

- Desenvolver uma interface web responsiva e acessível.
- Permitir cadastro, retirada, devolução e reserva de chaves.
- Fornecer controle e rastreabilidade via registros e histórico.
- Implementar autenticação de administrador e usuários comuns.
- Registrar logs para auditoria.
- Garantir segurança e proteção de dados com criptografia.
- Oferecer filtros de busca e relatórios detalhados.
- Permitir agendamentos remotos por usuários cadastrados.

---

## 🛠️ Funcionalidades

- [x] Cadastro de chaves com nome, local e tipo.
- [x] Registro de retirada e devolução de chaves com data e hora.
- [x] Cadastro e autenticação de usuários (servidores, alunos etc.).
- [x] Histórico de movimentações por chave e por usuário.
- [x] Sistema de reserva online com confirmação presencial.
- [x] Interface responsiva para acesso via celular/tablet.
- [x] Área de administração com filtros e geração de relatórios.
- [x] Registro manual para usuários não cadastrados.
- [x] Logs de auditoria de todas as ações realizadas.
- [x] Segurança com criptografia de senhas e backups.

---

## ⚙️ Tecnologias Utilizadas

| Tecnologia     | Finalidade                              |
|----------------|------------------------------------------|
| PHP            | Lógica de programação (Back-end)         |
| MySQL          | Banco de dados relacional                |
| HTML5/CSS3     | Estrutura e estilo das páginas           |
| JavaScript     | Funcionalidades dinâmicas no Front-end   |
| Bootstrap      | Interface responsiva                     |
| Scrum          | Metodologia ágil de desenvolvimento      |

---

## 🔐 Acesso ao Sistema

**Usuário Administrador:**  
- Pode cadastrar chaves  
- Acessar relatórios administrativos  
- Confirmar retiradas agendadas  
- Visualizar o histórico completo de uso  

**Usuário Comum (aluno/servidor):**  
- Pode realizar reservas de chaves  
- Visualizar seu histórico pessoal de agendamentos e retiradas  

---

## 🧪 Testes

Durante o desenvolvimento, foram realizados os seguintes testes:

- ✅ **Testes unitários:** verificação de componentes individuais do sistema  
- ✅ **Testes funcionais:** avaliação integrada das funcionalidades do sistema  
- ✅ **Validações com usuários reais:** testes com alunos e servidores da instituição para aferição prática  

---

## 🧱 Estrutura do Banco de Dados

Principais entidades do sistema:

- `usuario` – alunos, servidores ou administradores  
- `chave` – identificação e localização da chave física  
- `retirada` – controle de retirada e devolução das chaves  
- `reserva` – informações de agendamento por data e hora  

Os relacionamentos entre as entidades seguem os princípios de **integridade referencial**, garantindo segurança e consistência dos dados.

---

## 📊 Relatórios e Logs

O sistema oferece funcionalidades administrativas avançadas, incluindo:

- Exportação do histórico de uso das chaves  
- Filtros por data, usuário ou chave para reservas e movimentações  
- Visualização de logs de todas as ações realizadas no sistema  
- Confirmação presencial de retiradas previamente agendadas  

---

## 🧩 Requisitos Não Funcionais

- Interface amigável, intuitiva e responsiva  
- Compatibilidade com os principais navegadores modernos  
- Alta disponibilidade (mínimo de 99% online)  
- Estrutura modular, facilitando manutenção e atualizações  
- Conformidade com boas práticas de acessibilidade e usabilidade  
- Implementação de backups periódicos e segurança dos dados  

---

## 📜 Direitos Autorais

**Chave Mestra - Sistema de Controle de Chaves**  
**Autores:** Thiago Pucinelli e Leonardo Oliveira  
© 2025 Thiago Pucinelli & Leonardo Oliveira  
**Todos os direitos reservados.**

Este projeto é uma obra **exclusivamente autoral**, desenvolvida como parte de um **Trabalho de Conclusão de Curso (TCC)** do Instituto Federal Sul-Rio-Grandense – Campus Pelotas Visconde da Graça.

### ❌ É expressamente proibido:

- Copiar, redistribuir ou publicar qualquer parte do código  
- Modificar ou criar obras derivadas a partir deste projeto  
- Utilizar o código ou partes dele para fins pessoais, educacionais, comerciais ou institucionais sem autorização prévia por escrito dos autores  

> ⚠️ Este repositório é disponibilizado **apenas para leitura e consulta técnica**, com fins exclusivamente acadêmicos e demonstrativos.  
**Não constitui uma licença de uso.**

A violação destas condições constitui infração de direitos autorais conforme previsto na **Lei nº 9.610/1998 (Brasil)**.

---

## 📫 Contato

Caso deseje entrar em contato com os autores:

- ✉️ thiagopucinellisenac@gmail.com
- 💼 LinkedIn www.linkedin.com/in/thiagopucinelli
  
--- 


