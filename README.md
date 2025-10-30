# Inertia Blueprint 🧩

**Inertia Blueprint** is a powerful Laravel package that generates beautiful, fully-functional Inertia.js + React + TypeScript pages with **shadcn/ui** components from simple JSON file. Build complete CRUD interfaces in seconds.

## ✨ Features

- ⚡ **Rapid Development** - Generate complete CRUD pages in seconds
- 🎨 **Beautiful UI** - Pre-styled with shadcn/ui components
- 🔍 **Smart Search** - Debounced search with URL state management
- 📱 **Responsive Design** - Mobile-first, responsive layouts
- 🛡️ **Type Safety** - Full TypeScript support with proper typing
- 📝 **Form Handling** - Inertia.js `useForm` integration
- ⚙️ **Flexible Configuration** - Customize fields, routes, and behavior
- 🎯 **Customizable Stubs** - Publish and modify templates to fit your needs

### Generated Pages

- **Index** - Tables with search and actions
- **Create** - Form pages with validation and error handling
- **Edit** - Pre-populated forms with validation and update functionality
- **View** - Detailed view pages with clean layouts

---

## 📦 Installation

Install via Composer:

```bash
composer require sediqzada/inertia-blueprint:^0.1@beta --dev
```

### Prerequisites

Make sure you have the following set up in your Laravel project:

- **Laravel** 10+ with Inertia.js
- **React** 18+ with TypeScript support
- **shadcn/ui** components installed and configured
- **Tailwind CSS** for styling

### Recommended Setup

For the best experience, ensure your project has:

```bash
# Install shadcn/ui if not already installed
npx shadcn-ui@latest init

# Install required shadcn/ui components
npx shadcn-ui@latest add button card input label textarea checkbox select dialog table
```

---

## 🚀 Quick Start

### 1. Create a Blueprint File

Create a `blueprint.json` file in your project root:

```json
{
    "model": "Post",
    "fields": [
        { "name": "title", "type": "string", "inputType": "text", "searchable": true },
        { "name": "content", "type": "text", "inputType": "textarea", "searchable": true },
        { "name": "published_at", "type": "datetime", "inputType": "text" },
        {
            "name": "category",
            "fieldName": "category_id",
            "type": "string",
            "inputType": "select",
            "options": "categories",
            "valueField": "id",
            "labelField": "name"
        }
    ],
    "pages": ["index", "create", "edit", "view"]
}
```

### 2. Generate Your Pages

```bash
php artisan blueprint:generate
```

### 3. Handle Existing Files

If pages already exist, you'll be prompted to choose:

- **ignore** - Skip existing files, only generate new ones
- **override** - Replace existing files with new versions

### 4. That's It!

Your pages are now available in `resources/js/pages/Post/` with:

- Full TypeScript support
- shadcn/ui components
- Form validation
- Search functionality
- Responsive design

---

## 📋 Configuration Reference

### Basic Structure

```json
{
  "model": "ModelName",
  "fields": [...],
  "routes": {...},
  "language": "ts",
  "pages": [...]
}
```

### Field Configuration

Each field supports the following properties:

```json
{
  "name": "fieldName",                    // Required: Database field name
  "type": "string|text|number|boolean|datetime|file|select",  // Required: Data type
  "inputType": "text|textarea|number|checkbox|file|select",   // Required: Input component type
  "searchable": true|false,                // Optional: Include in search functionality
  "options": {...}                         // Required for select fields
}
```

#### Field Types & Input Types

| Type       | Input Type | Generated Component    | Description                   | Example                        |
| ---------- | ---------- | ---------------------- | ----------------------------- | ------------------------------ |
| `string`   | `text`     | `Input`                | Single-line text input        | Name, email, slug              |
| `string`   | `textarea` | `Textarea`             | Multi-line text area          | Description, content, notes    |
| `number`   | `number`   | `Input[type="number"]` | Numeric input with validation | Price, quantity, age           |
| `boolean`  | `checkbox` | `Checkbox`             | Boolean checkbox              | Is active, published, featured |
| `datetime` | `text`     | `Input[type="text"]`   | Date/time input               | Created at, published at       |
| `string`   | `file`     | `Input[type="file"]`   | File upload input             | Images, documents, attachments |
| `string`   | `select`   | `Select`               | Dropdown selection            | Categories, status, user roles |

#### Select Field Configuration

Select fields require additional configuration for data binding:

If the options are provided by controller:

```json
{
            "name": "category",
            "fieldName": "category_id",
            "type": "string",
            "inputType": "select",
            "options": "categories",
            "valueField": "id",
            "labelField": "name"
}
```

Or if the options are static:

```json
{
    "name": "status",
    "type": "string",
    "inputType": "select",
    "options": [
        { "status": "draft", "label": "Draft" },
        { "status": "published", "label": "Published" },
        { "status": "cancelled", "label": "Cancelled" },
        { "status": "completed", "label": "Completed" }
    ],
    "valueField": "status",
    "labelField": "label"
}
```

#### Field Examples

```json
{
    "fields": [
        // Basic text field with search
        {
            "name": "title",
            "type": "string",
            "inputType": "text",
            "searchable": true
        },

        // Large text area
        {
            "name": "description",
            "type": "string",
            "inputType": "textarea",
            "searchable": true
        },

        // Numeric field
        {
            "name": "price",
            "type": "number",
            "inputType": "number"
        },

        // Boolean checkbox
        {
            "name": "is_featured",
            "type": "boolean",
            "inputType": "checkbox"
        },

        // File upload
        {
            "name": "featured_image",
            "type": "string",
            "inputType": "file"
        },

        // Select dropdown
        {
            "name": "status",
            "type": "string",
            "inputType": "select",
            "options": "statuses",
            "valueField": "value",
            "labelField": "label"
        }
    ]
}
```

### Routes Configuration

Routes will be generated from the model key, for example if the "model: Post" the generated routes will be:

`posts.index,
posts.create,
posts.store,
posts.show,
posts.edit,
posts.update,
posts.destroy`

You can also define your Laravel route names that correspond to your controller methods:

```json
{
    "routes": {
        "index": "posts.index", // GET /posts - List all posts
        "create": "posts.create", // GET /posts/create - Show create form
        "store": "posts.store", // POST /posts - Store new post
        "show": "posts.show", // GET /posts/{id} - Show single post
        "edit": "posts.edit", // GET /posts/{id}/edit - Show edit form
        "update": "posts.update", // PUT/PATCH /posts/{id} - Update post
        "destroy": "posts.destroy" // DELETE /posts/{id} - Delete post
    }
}
```

**Route Requirements by Page:**

| Page     | Required Routes    | Optional Routes |
| -------- | ------------------ | --------------- |
| `index`  | `index`, `destroy` | `show`, `edit`  |
| `create` | `store`, `index`   | -               |
| `edit`   | `update`, `index`  | -               |
| `view`   | `index`            | `edit`          |

### Pages Configuration

Choose which pages to generate. Each page type serves a specific purpose:

```json
{
    "pages": ["index", "create", "edit", "view"]
}
```

**Available Page Types:**

| Page     | Purpose                | Features                                        |
| -------- | ---------------------- | ----------------------------------------------- |
| `index`  | List/table view        | Search, delete confirmation                     |
| `create` | New record form        | Form validation, file uploads, select dropdowns |
| `edit`   | Update existing record | Pre-populated form, validation, file handling   |
| `view`   | Read-only detail view  | Clean layout, formatted data display            |

**Language Support:**

- **TypeScript (`ts`)** - Full type safety, interfaces, proper typing (default and currently only supported option)

---

## 🎯 Advanced Usage

### Custom Blueprint Files

Generate from specific blueprint files:

```bash
# Use a custom blueprint file
php artisan blueprint:generate custom-blueprint.json

# Generate multiple models
php artisan blueprint:generate posts.json
php artisan blueprint:generate users.json
php artisan blueprint:generate categories.json
```

### Publishing and Customizing Stubs

Customize the generated code templates to match your project's needs:

```bash
# Publish stub files for customization
php artisan blueprint:publish-stubs

# Force overwrite existing stubs
php artisan blueprint:publish-stubs --force
```

This publishes stub files to `resources/inertia-blueprint-stubs/`:

### Stub Structure

After publishing, you'll find these customizable templates:

```
resources/inertia-blueprint-stubs/
└── react/
    ├── Index.stub      # List/table view template
    ├── Create.stub     # Creation form template
    ├── Edit.stub       # Edit form template
    └── View.stub       # Detail view template
```

## 📁 Generated File Structure

After running the command, your files will be organized as:

```
resources/js/pages/
└── Post/
    ├── Index.tsx    # List view with search & actions
    ├── Create.tsx   # Creation form
    ├── Edit.tsx     # Edit form
    └── View.tsx     # Detail view
```

## 🛠️ Configuration

### Publishing Configuration

Publish the configuration file to customize default settings:

```bash
php artisan vendor:publish --provider="Sediqzada\InertiaBlueprint\InertiaBluerintServiceProvider"
```

This creates `config/inertia-blueprint.php`:

```php
<?php

return [
    'default_language' => 'ts', // TypeScript (only supported language)
];
```

## 🧪 Complete Examples

### Example 1: Blog Management System

**blog-posts.json**

```json
{
    "model": "Post",
    "fields": [
        {
            "name": "title",
            "type": "string",
            "inputType": "text",
            "searchable": true
        },
        {
            "name": "slug",
            "type": "string",
            "inputType": "text"
        },
        {
            "name": "content",
            "type": "string",
            "inputType": "textarea",
            "searchable": true
        },
        {
            "name": "excerpt",
            "type": "string",
            "inputType": "textarea"
        },
        {
            "name": "featured_image",
            "type": "file",
            "inputType": "file"
        },
        {
            "name": "is_published",
            "type": "boolean",
            "inputType": "checkbox"
        },
        {
            "name": "category",
            "fieldName": "category_id",
            "type": "string",
            "inputType": "select",
            "options": "categories",
            "valueField": "id",
            "labelField": "name"
        }
    ],
    "routes": {
        "index": "admin.posts.index",
        "create": "admin.posts.create",
        "store": "admin.posts.store",
        "show": "admin.posts.show",
        "edit": "admin.posts.edit",
        "update": "admin.posts.update",
        "destroy": "admin.posts.destroy"
    },
    "pages": ["index", "create", "edit", "view"]
}
```

### Example 2: E-commerce Product Management

**products.json**

```json
{
    "model": "Product",
    "fields": [
        {
            "name": "name",
            "type": "string",
            "inputType": "text",
            "searchable": true
        },
        {
            "name": "sku",
            "type": "string",
            "inputType": "text",
            "searchable": true
        },
        {
            "name": "description",
            "type": "text",
            "inputType": "textarea"
        },
        {
            "name": "price",
            "type": "number",
            "inputType": "number"
        },
        {
            "name": "stock_quantity",
            "type": "number",
            "inputType": "number"
        },
        {
            "name": "is_active",
            "type": "boolean",
            "inputType": "checkbox"
        },
        {
            "name": "category",
            "fieldName": "category_id",
            "type": "string",
            "inputType": "select",
            "options": "categories",
            "valueField": "id",
            "labelField": "name"
        },
        {
            "name": "brand",
            "type": "string",
            "inputType": "select",
            "options": "brands",
            "valueField": "id",
            "labelField": "name"
        },
        {
            "name": "product_image",
            "type": "file",
            "inputType": "file"
        }
    ],
    "routes": {
        "index": "admin.products.index",
        "create": "admin.products.create",
        "store": "admin.products.store",
        "show": "admin.products.show",
        "edit": "admin.products.edit",
        "update": "admin.products.update",
        "destroy": "admin.products.destroy"
    },
    "pages": ["index", "create", "edit", "view"]
}
```

### Example 3: User Management

**users.json**

```json
{
    "model": "User",
    "fields": [
        {
            "name": "first_name",
            "type": "string",
            "inputType": "text",
            "searchable": true
        },
        {
            "name": "last_name",
            "type": "string",
            "inputType": "text",
            "searchable": true
        },
        {
            "name": "email",
            "type": "string",
            "inputType": "text",
            "searchable": true
        },
        {
            "name": "phone",
            "type": "string",
            "inputType": "text"
        },
        {
            "name": "bio",
            "type": "text",
            "inputType": "textarea"
        },
        {
            "name": "avatar",
            "type": "file",
            "inputType": "file"
        },
        {
            "name": "status",
            "type": "string",
            "inputType": "select",
            "options": [
                { "status": "draft", "label": "Draft" },
                { "status": "published", "label": "Published" },
                { "status": "cancelled", "label": "Cancelled" },
                { "status": "completed", "label": "Completed" }
            ],
            "valueField": "status",
            "labelField": "label"
        }
    ],
    "routes": {
        "index": "admin.users.index",
        "create": "admin.users.create",
        "store": "admin.users.store",
        "show": "admin.users.show",
        "edit": "admin.users.edit",
        "update": "admin.users.update",
        "destroy": "admin.users.destroy"
    },
    "pages": ["index", "create", "edit", "view"]
}
```

### Laravel Controller Requirements

Your Laravel controllers should provide the expected data structure:

```php
// Index method
public function index(Request $request)
{
    $query = Post::query();

    // Handle search
    if ($request->search) {
        $query->where('title', 'like', "%{$request->search}%")
              ->orWhere('content', 'like', "%{$request->search}%");
    }

    $posts = $query->get();

    return inertia('Post/Index', [
        'posts' => $posts,
        'search' => $request->search,
        'categories' => Category::all(['id', 'name']), // For select fields
    ]);
}

// Create method
public function create()
{
    return inertia('Post/Create', [
        'categories' => Category::all(['id', 'name']),
    ]);
}

// Edit method
public function edit(Post $post)
{
    return inertia('Post/Edit', [
        'post' => $post,
        'categories' => Category::all(['id', 'name']),
    ]);
}

// Show method
public function show(Post $post)
{
    return inertia('Post/Show', [
        'post' => $post,
    ]);
}
```

## 📚 Best Practices

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `composer test`

### Running Tests

```bash
# Run tests

composer test
# Or
./vendor/bin/phpunit

# Run pint
./vendor/bin/pint --test

# Run rector
./vendor/bin/rector --dry-run

# Run phpstan
./vendor/bin/phpstan

```

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

## 🙏 Credits

- **shadcn/ui** - For the beautiful component library
- **Inertia.js** - For the seamless SPA experience
- **Laravel** - For the amazing framework
- **React** - For the powerful frontend library

---

## 📞 Support

If you discover any bugs, please create an issue on GitHub and **DO NOT FORGET TO STAR THE REPO**.

---

**Built with ❤️ for the Laravel community**
