<template>
  <div class="json-viewer">
    <div v-if="isObject(parsedData)" class="json-container">
      <div
        v-for="(value, key) in parsedData"
        :key="key"
        class="json-item"
      >
        <div
          class="json-key"
          @click="toggleCollapse(getPath(path, key))"
          :class="{ collapsible: isObject(value) }"
        >
          <span class="toggle-icon" v-if="isObject(value)">
            {{ isCollapsed(getPath(path, key)) ? '▶' : '▼' }}
          </span>
          <span>{{ key }}:</span>
          <span v-if="!isObject(value)" class="json-value">{{ formatValue(value) }}</span>
        </div>
        <div v-if="isObject(value) && !isCollapsed(getPath(path, key))" class="json-nested">
          <JsonViewer :data="value" :path="getPath(path, key)" :collapsed-paths="collapsedPaths" @update:collapsed-paths="$emit('update:collapsed-paths', $event)" />
        </div>
      </div>
    </div>
    <div v-else-if="Array.isArray(parsedData)" class="json-container">
      <div
        v-for="(value, index) in parsedData"
        :key="index"
        class="json-item"
      >
        <div
          class="json-key"
          @click="toggleCollapse(getPath(path, index))"
          :class="{ collapsible: isObject(value) }"
        >
          <span class="toggle-icon" v-if="isObject(value)">
            {{ isCollapsed(getPath(path, index)) ? '▶' : '▼' }}
          </span>
          <span>{{ index }}:</span>
          <span v-if="!isObject(value)" class="json-value">{{ formatValue(value) }}</span>
        </div>
        <div v-if="isObject(value) && !isCollapsed(getPath(path, index))" class="json-nested">
          <JsonViewer :data="value" :path="getPath(path, index)" :collapsed-paths="collapsedPaths" @update:collapsed-paths="$emit('update:collapsed-paths', $event)" />
        </div>
      </div>
    </div>
    <div v-else class="json-primitive">
      {{ formatValue(parsedData) }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'JsonViewer',
  props: {
    data: {
      type: [Object, Array, String, Number, Boolean, null],
      required: true
    },
    path: {
      type: String,
      default: 'root'
    },
    collapsedPaths: {
      type: Array,
      default: () => []
    }
  },
  emits: ['update:collapsed-paths'],
  computed: {
    parsedData() {
      if (typeof this.data === 'string') {
        try {
          return JSON.parse(this.data);
        } catch (e) {
          return this.data;
        }
      }
      return this.data;
    }
  },
  methods: {
    isObject(value) {
      return value !== null && (typeof value === 'object' || Array.isArray(value));
    },
    formatValue(value) {
      if (value === null) return 'null';
      if (value === undefined) return 'undefined';
      if (typeof value === 'string') return `"${value}"`;
      return value.toString();
    },
    getPath(parentPath, key) {
      return `${parentPath}.${key}`;
    },
    isCollapsed(path) {
      return this.collapsedPaths.includes(path);
    },
    toggleCollapse(path) {
      if (!this.isObject(this.getValueAtPath(path))) return;
      
      const newCollapsedPaths = [...this.collapsedPaths];
      const index = newCollapsedPaths.indexOf(path);
      
      if (index >= 0) {
        newCollapsedPaths.splice(index, 1);
      } else {
        newCollapsedPaths.push(path);
      }
      
      this.$emit('update:collapsed-paths', newCollapsedPaths);
    },
    getValueAtPath(path) {
      if (path === 'root') return this.parsedData;
      
      const parts = path.split('.');
      parts.shift(); // Remove 'root'
      
      let current = this.parsedData;
      for (const part of parts) {
        if (current[part] === undefined) return undefined;
        current = current[part];
      }
      
      return current;
    }
  }
};
</script>

<style scoped>
.json-viewer {
  font-family: monospace;
  font-size: 14px;
  background-color: #f8f8f8;
  border-radius: 3px;
  padding: 8px;
  overflow: auto;
}

.json-container {
  padding-left: 20px;
}

.json-item {
  margin: 4px 0;
}

.json-key {
  color: #881391;
  cursor: default;
}

.json-key.collapsible {
  cursor: pointer;
}

.json-key.collapsible:hover {
  text-decoration: underline;
}

.json-nested {
  margin-left: 20px;
  border-left: 1px dashed #ccc;
  padding-left: 10px;
}

.json-value {
  color: #1a1aa6;
  margin-left: 8px;
}

.toggle-icon {
  display: inline-block;
  width: 12px;
  height: 12px;
  margin-right: 5px;
  font-size: 10px;
  color: #555;
}

.json-primitive {
  color: #1a1aa6;
}
</style>