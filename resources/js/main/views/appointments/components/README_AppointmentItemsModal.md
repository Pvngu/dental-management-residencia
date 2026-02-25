# Appointment Items Modal Component

## Overview
The `AppointmentItemsModal` component allows users to track and record items used during appointments. This component is displayed when an appointment is in "In Progress" status.

## Location
`resources/js/main/views/appointments/components/AppointmentItemsModal.vue`

## Features

### 1. Search and Filter
- **Search Items**: Search by item name, SKU, or description
- **Category Filter**: Filter items by category (Dental Materials, Surgical Instruments, Disposables, Medications, Lab Supplies)

### 2. Available Items Table
Displays all available items with:
- Item name and SKU
- Category
- Available quantity with visual indicators (green for in stock, red for out of stock)
- Description
- Add button (disabled if item is out of stock)

### 3. Selected Items Management
- Add items from available list with quantity selector
- Adjust quantities (respects maximum available quantity)
- Remove items from selection
- View unit of measurement for each item

### 4. Notes Section
Add notes about the items used during the appointment

### 5. Save Functionality
- Save button is disabled until at least one item is selected
- Validates that at least one item is selected before saving
- Shows loading state during save operation

## Usage

### In TodaysAppointments.vue

The component is integrated into the appointments flow:

```vue
<!-- Add Items button (shown only when status is "in_progress") -->
<a-button
    v-if="getStatusForItem(item) === 'in_progress'"
    type="default"
    @click="handleOpenItemsModal(item)"
    style="border-color: #722ed1; color: #722ed1;"
>
    <template #icon>
        <ShoppingCartOutlined />
    </template>
    {{ t('appointments.add_items') || 'Add Items' }}
</a-button>

<!-- Modal Component -->
<AppointmentItemsModal
    :visible="itemsModalVisible"
    :appointment="currentAppointmentForItems"
    @saved="handleItemsSaved"
    @closed="handleItemsModalClosed"
/>
```

### Event Handlers

```javascript
// Open the modal
const handleOpenItemsModal = (appointment) => {
    currentAppointmentForItems.value = appointment;
    itemsModalVisible.value = true;
};

// Handle save event
const handleItemsSaved = (data) => {
    console.log('Items saved for appointment:', data);
    fetchTodaysAppointments(); // Refresh the list
};

// Handle close event
const handleItemsModalClosed = () => {
    itemsModalVisible.value = false;
    currentAppointmentForItems.value = null;
};
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| visible | Boolean | false | Controls modal visibility |
| appointment | Object | {} | Current appointment object |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| update:visible | Boolean | Emitted when modal visibility changes |
| saved | Object | Emitted when items are saved successfully |
| closed | - | Emitted when modal is closed |

## Data Structure

### Saved Data Format

```javascript
{
    appointment_xid: 'appointment-xid-123',
    items: [
        {
            item_xid: 'item-xid-456',
            item_name: 'Composite Resin A2',
            quantity: 5,
            unit: 'g'
        },
        // ... more items
    ],
    notes: 'Optional notes about items used'
}
```

## Dummy Data

The component currently uses dummy data for demonstration purposes:

### Categories
- Dental Materials
- Surgical Instruments
- Disposables
- Medications
- Lab Supplies

### Sample Items
1. Composite Resin A2 (Dental Materials)
2. Anesthetic Cartridges (Medications)
3. Surgical Gloves (Disposables)
4. Dental Mirror (Surgical Instruments)
5. Cotton Rolls (Disposables)
6. Bonding Agent (Dental Materials)
7. Articulating Paper (Disposables)
8. Dental Bur Kit (Surgical Instruments)
9. Alginate Impression Material (Lab Supplies)
10. Hydrogen Peroxide Solution (Medications) - Out of Stock

## Backend Integration (TODO)

When implementing the backend, replace the dummy data and save logic:

### API Endpoints Needed

```
GET /api/items - Fetch available items
POST /api/appointments/items - Save items used in appointment
```

### Implementation Steps

1. Replace dummy `availableItems` with API call:
```javascript
const fetchItems = async () => {
    loading.value = true;
    try {
        const response = await axiosAdmin.get('items?fields=id,xid,name,sku,category,unit,available_quantity,description,type');
        availableItems.value = response.data;
    } catch (error) {
        console.error('Error fetching items:', error);
        message.error(t('common.error'));
    } finally {
        loading.value = false;
    }
};
```

2. Replace save logic in `handleSave`:
```javascript
const handleSave = async () => {
    // ... validation code ...
    
    try {
        saving.value = true;
        const response = await axiosAdmin.post('appointments/items', data);
        
        message.success(t('appointments.items_saved'));
        emit('saved', response.data);
        onClose();
    } catch (error) {
        console.error('Error saving appointment items:', error);
        message.error(t('common.error'));
    } finally {
        saving.value = false;
    }
};
```

## Translations

New translation keys added to `LangTrans.php`:

```php
'appointments' => [
    // ... existing translations ...
    'items_used' => 'Items Used During Appointment',
    'available_items' => 'Available Items',
    'selected_items' => 'Selected Items',
    'no_items_selected' => 'No items selected',
    'add_items' => 'Add Items',
    'items_notes_placeholder' => 'Add notes about items used...',
    'item_already_added' => 'Item already added',
    'item_added' => 'Item added successfully',
    'item_removed' => 'Item removed',
    'items_saved' => 'Items saved successfully',
    'select_at_least_one_item' => 'Please select at least one item',
],
```

## Styling

The component includes:
- Responsive layout with proper spacing
- Custom scrollbar styling for better UX
- Disabled state styling for out-of-stock items
- Color-coded tags for item availability
- Loading states for all async operations

## Future Enhancements

1. **Backend Integration**: Connect to real API endpoints
2. **Inventory Tracking**: Update inventory levels when items are used
3. **Cost Tracking**: Calculate total cost of items used
4. **History**: View previously used items for this appointment
5. **Barcode Scanner**: Add items by scanning barcodes
6. **Batch Selection**: Select multiple items at once
7. **Templates**: Save commonly used item combinations as templates
8. **Reports**: Generate reports of items usage by appointment type
