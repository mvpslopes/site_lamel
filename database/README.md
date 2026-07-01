# LaMel - Banco de Dados

## Como instalar na Hostinger

1. Acesse o **phpMyAdmin** no painel da Hostinger
2. Selecione o banco `u179630068_lamel_bd`
3. Abra a aba **SQL**
4. Execute os scripts **nesta ordem**:
   - `01_schema.sql` — cria as tabelas
   - `02_seed.sql` — insere usuário root e catálogo inicial

## Usuário Root padrão

| Campo | Valor |
|-------|-------|
| Usuário | `marcus.lopes` |
| Senha | `*.Admin14!` |
| Perfil | Root |

## Perfis de acesso

| Perfil | Permissões |
|--------|------------|
| **Root** | Tudo + criar/editar usuários |
| **Admin** | Produtos e coleções |

## Tabelas criadas

- `users` — usuários do painel interno
- `collections` — coleções do catálogo
- `products` — produtos
- `product_images` — galeria de imagens dos produtos

## Painel interno

Após publicar os arquivos no servidor, acesse:

`https://lamelmodas.com.br/admin/`

## Configuração PHP

No servidor, confirme que existe o arquivo:

`admin/config/config.local.php`

Use `admin/config/config.example.php` como modelo se necessário.
