<template>
    <div
        :class="toothClasses"
        :style="toothStyle"
        @click="handleClick"
    >
        <canvas
            ref="toothCanvas"
            :class="canvasClasses"
            :width="size"
            :height="size"
            @contextmenu.prevent
        />
    </div>
</template>

<script>
import { ref, onMounted, watch, computed, nextTick } from 'vue';
import useToothImageCache from '../composables/useToothImageCache.js';

export default {
    name: "ToothComponent",
    props: {
        tooth: {
            type: Object,
            required: true,
        },
        type: {
            type: String,
            default: "upper",
            validator: (value) => ["upper", "lower"].includes(value),
        },
        view: {
            type: String,
            default: "front",
            validator: (value) => ["front", "top", "upside-down"].includes(value),
        },
        currentCondition: {
            type: String,
            default: "",
        },
        currentSurface: {
            type: String,
            default: "",
        },
        size: {
            type: [Number, String],
            default: "50%",
        },
        invertLowerViews: {
            type: Boolean,
            default: true,
        },
    },

    setup(props, { emit }) {
        const toothCanvas = ref(null);
        const originalImageData = ref(null);
        
        const { getImageFromCache, getToothImageUrl, setCachedImage } = useToothImageCache();

        // Computed properties for cleaner template
        const toothClasses = computed(() => [
            'tooth',
            { selected: props.tooth.selected },
            { missing: props.tooth.conditions?.missing },
            { implant: props.tooth.restoration?.crown?.crownBase === "implant" && effectiveView.value !== "top" }
        ]);

        const toothStyle = computed(() => ({
            width: typeof props.size === 'string' ? props.size : `${props.size}px`,
        }));

        const canvasClasses = computed(() => [
            'tooth-canvas',
            { 'top-view': effectiveView.value === 'top' }
        ]);

        // Helper functions
        const getToothType = () => (props.tooth.id >= 11 && props.tooth.id <= 28) ? 'upper' : 'lower';

        // Computed property to handle view inversion for lower teeth
        const effectiveView = computed(() => {
            const isLowerTooth = getToothType() === 'lower';
            if (!isLowerTooth || !props.invertLowerViews) return props.view;

            // For lower teeth, invert front and upside-down views
            switch(props.view) {
                case 'front': return 'upside-down';
                case 'upside-down': return 'front';
                default: return props.view;
            }
        });

        const getViewSuffix = () => {
            switch(effectiveView.value) {
                case "top": return "-top";
                case "upside-down": return "-ud";
                default: return "";
            }
        };

        const getImageVariant = () => {
            if (props.tooth.conditions?.missing) return "-missing";
            if (props.tooth.conditions?.top_missing) return "-top-missing";
            // Implant images only apply to front and upside-down views, not top view
            if (props.tooth.restoration?.crown?.crownBase === "implant" && effectiveView.value !== "top") {
                return "-implant";
            }
            return "";
        };

        const toothImageUrl = computed(() => {
            const type = getToothType();
            const viewSuffix = getViewSuffix();
            const variant = getImageVariant(); 
            return getToothImageUrl(type, props.tooth.id, viewSuffix, variant);
        });

        const getFallbackImageUrl = () => {
            const viewSuffix = effectiveView.value === "top" ? "_top" : "";
            const type = getToothType();
            return `/images/dental-chart/realistic/${type}${viewSuffix}_default.png`;
        };

        // Image loading utilities
        const loadAndCacheImage = async (type, id, viewSuffix, variant, url) => {
            const cachedImage = getImageFromCache(type, id, viewSuffix, variant);
            if (cachedImage) return cachedImage; 

            return new Promise((resolve) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.loading = 'eager';
                
                const timeout = setTimeout(() => {
                    img.onload = img.onerror = null;
                    resolve(null);
                }, 5000);
                
                img.onload = () => {
                    clearTimeout(timeout);
                    img.onload = img.onerror = null;
                    setCachedImage(type, id, viewSuffix, variant, img);
                    resolve(img);
                };
                
                img.onerror = () => {
                    clearTimeout(timeout);
                    img.onload = img.onerror = null;
                    console.warn('Failed to load tooth image:', { type, id, viewSuffix, variant, url });
                    resolve(null);
                };
                
                img.src = url;
            });
        };

        const loadFallbackImage = async (canvas, ctx) => {
            const fallbackImg = new Image();
            fallbackImg.crossOrigin = 'anonymous';
            fallbackImg.loading = 'eager';
            
            return new Promise((resolve) => {
                const timeout = setTimeout(() => {
                    fallbackImg.onload = fallbackImg.onerror = null;
                    resolve(null);
                }, 3000);
                
                fallbackImg.onload = () => {
                    clearTimeout(timeout);
                    fallbackImg.onload = fallbackImg.onerror = null;
                    if (toothCanvas.value) {
                        drawImageToCanvas(fallbackImg, canvas, ctx);
                    }
                    resolve(fallbackImg);
                };
                
                fallbackImg.onerror = () => {
                    clearTimeout(timeout);
                    fallbackImg.onload = fallbackImg.onerror = null;
                    resolve(null);
                };
                
                fallbackImg.src = getFallbackImageUrl();
            });
        };

        // Canvas drawing utilities
        const drawImageToCanvas = (img, canvas, ctx) => {
            canvas.width = img.width;
            canvas.height = img.height;

            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.save();
            
            // Make image transparent for pontic crowns
            if (props.tooth.restoration?.crown?.crownType === "pontic") {
                ctx.globalAlpha = 0.3;
            }
            
            // Draw image with proper scaling
            const scale = Math.min(canvas.width / img.width, canvas.height / img.height);
            const scaledWidth = img.width * scale;
            const scaledHeight = img.height * scale;
            const x = (canvas.width - scaledWidth) / 2;
            const y = (canvas.height - scaledHeight) / 2;
            
            ctx.drawImage(img, x, y, scaledWidth, scaledHeight);
            ctx.restore();
            
            originalImageData.value = ctx.getImageData(0, 0, canvas.width, canvas.height);
            drawConditionsOverlay();
        };

        const drawConditionsOverlay = () => {
            if (!toothCanvas.value || !originalImageData.value) return;
            
            const canvas = toothCanvas.value;
            const ctx = canvas.getContext('2d');
            const centerX = canvas.width / 2;
            const centerY = canvas.height / 2;
            const scale = canvas.width / 100;
            
            // Restore original image
            ctx.putImageData(originalImageData.value, 0, 0);
            
            // Only draw conditions if tooth is not missing and not an implant (or if it's top view of implant)
            const isImplant = props.tooth.restoration?.crown?.crownBase === "implant" && effectiveView.value !== "top";
            const isPontic = props.tooth.restoration?.crown?.crownType === "pontic";
            if (!props.tooth.conditions?.missing && !isImplant) {
                drawConditions(ctx, centerX, centerY, scale);
                drawPathology(ctx, centerX, centerY, scale);
                drawRestorations(ctx, centerX, centerY, scale);
            } else if (isPontic) {
                // For pontic crowns, only draw restorations (crown overlay) since the tooth image is transparent
                drawRestorations(ctx, centerX, centerY, scale);
            }

            drawToBeExtractedBorder(ctx);
        };

        // Material color utility
        const getMaterialColor = (material) => {
            const colors = {
                composite: '#83c7e7',
                ceramic: '#b183e8',
                amalgam: '#b0b2b1',
                gold: '#fadd63',
                non_precious_metal: '#cccfd1',
                temporary: '#9beae6'
            };
            return colors[material] || '#95a5a6';
        };

        // Surface drawing utilities
        const shouldDrawSurface = (surface) => {
            if (surface === 'buccal' && effectiveView.value !== 'front') return false;
            if (surface === 'palatal' && effectiveView.value !== 'upside-down') return false;
            if ((surface === 'occlusal' || surface === 'incisal') && effectiveView.value !== 'top') return false;
            if (surface === 'cervical_buccal' && effectiveView.value !== 'front') return false;
            if (surface === 'cervical_palatal' && effectiveView.value !== 'upside-down') return false;
            return true;
        };

        const getSurfacePosition = (centerX, centerY, scale, surface) => {
            const offset = 20 * scale;
            
            switch (surface) {
                case 'buccal':
                    return { x: centerX + offset, y: centerY - offset };
                case 'lingual':
                case 'palatal':
                    return { x: centerX - offset, y: centerY - offset };
                case 'distal':
                    return { x: centerX - offset, y: centerY };
                case 'incisal':
                case 'occlusal':
                    return { x: centerX, y: centerY - offset };
                default:
                    return { x: centerX, y: centerY };
            }
        };

        const getSurfaceArea = (centerX, centerY, scale, surface) => {
            const size = 12 * scale;
            
            switch (surface) {
                case 'buccal': 
                    return { x: centerX, y: centerY + centerY * 0.70, width: size, height: size };
                case 'lingual':
                case 'palatal':
                    return { x: centerX, y: centerY + centerY * -0.70, width: size, height: size };
                case 'distal':
                    return { x: centerX, y: centerY + 15 * scale, width: size, height: size };
                case 'incisal':
                case 'occlusal':
                    return { x: centerX, y: centerY - 15 * scale, width: size, height: size };
                default:
                    return { x: centerX, y: centerY, width: size, height: size };
            }
        };

        // Condition drawing functions
        const drawConditions = (ctx, centerX, centerY, scale) => {
            const conditions = props.tooth.conditions;
            if (!conditions) return;

            // Draw pus indicators (blue dots)
            if (conditions.pus) {
                Object.entries(conditions.pus).forEach(([surface, hasPus]) => {
                    if (hasPus && shouldDrawSurface(surface)) {
                        drawConditionIndicator(ctx, centerX, centerY, scale, surface, '#1890ff', 'dot');
                    }
                });
            }

            // Draw inflammation indicators (red squares)
            if (conditions.inflammation) {
                Object.entries(conditions.inflammation).forEach(([surface, hasInflammation]) => {
                    if (hasInflammation && shouldDrawSurface(surface)) {
                        drawConditionIndicator(ctx, centerX, centerY, scale, surface, '#f5222d', 'square');
                    }
                });
            }
        };

        const drawPathology = (ctx, centerX, centerY, scale) => {
            const pathology = props.tooth.pathology;
            if (!pathology) return;

            // Draw decay
            if (pathology.decay?.selectedSurfaces?.length > 0) {
                pathology.decay.selectedSurfaces.forEach(surface => {
                    if (shouldDrawSurface(surface)) {
                        drawSurfaceCondition(ctx, centerX, centerY, scale, surface, '#E68074', 'fill');
                    }
                });
            }

            // Draw fractures
            if (pathology.fracture?.fractureType) {
                drawFracture(ctx, centerX, centerY, scale, pathology.fracture);
            }

            // Draw tooth wear
            if (pathology.toothWear?.toothWearSurfaces?.length > 0) {
                drawToothWear(ctx, centerX, centerY, scale, pathology.toothWear);
            }

            // Draw discoloration
            if (pathology.discoloration?.discolorationColor) {
                drawDiscoloration(ctx, centerX, centerY, scale, pathology.discoloration.discolorationColor);
            }

            // Draw apical pathology
            if (pathology.apical?.pulpType) {
                drawApicalPathology(ctx, centerX, centerY, scale);
            }
        };

        const drawRestorations = (ctx, centerX, centerY, scale) => {
            const restoration = props.tooth.restoration;
            if (!restoration) return;

            // Draw filling restorations
            if (restoration.filling?.selectedSurfaces?.length > 0) {
                const material = restoration.filling.material;
                const color = getMaterialColor(material);

                restoration.filling.selectedSurfaces.forEach(surface => {
                    if (shouldDrawSurface(surface)) {
                        drawSurfaceCondition(ctx, centerX, centerY, scale, surface, color, 'fill');
                    }
                });
            }

            // Draw veneer restorations
            if (restoration.veneer?.selectedSurfaces?.length > 0) {
                const material = restoration.veneer.material;
                const color = getMaterialColor(material);
                const selectedSurface = restoration.veneer.selectedSurfaces[0]; // Only one surface can be selected

                if (selectedSurface === 'palatal') {
                    drawVeneerPalatal(ctx, centerX, centerY, scale, color);
                } else if (selectedSurface === 'buccal') {
                    drawVeneerBuccal(ctx, centerX, centerY, scale, color);
                }
            }

            // Draw crown restoration with discoloration-style overlay
            if (restoration.crown) {
                // Apply discoloration overlay for crown (same canvas style as pathology discoloration)
                drawDiscoloration(ctx, centerX, centerY, scale, restoration.crown?.material);
            }

            // Draw other restorations (beneath crown if present)
            const restorations = props.tooth.restorations;
            if (restorations) {
                if (restorations.root_canal) drawRootCanal(ctx, centerX, centerY, scale);
                if (restorations.implant) drawImplant(ctx, centerX, centerY, scale);
            }
        };

        // Specific drawing functions
        const drawConditionIndicator = (ctx, centerX, centerY, scale, surface, color, type) => {
            // Special handling for occlusal and incisal surfaces in top view
            if ((surface === 'occlusal' || surface === 'incisal') && effectiveView.value === 'top') {
                ctx.fillStyle = color;
                ctx.strokeStyle = color;

                if (type === 'dot') {
                    ctx.beginPath();
                    ctx.arc(centerX, centerY, 4 * scale, 0, 2 * Math.PI);
                    ctx.fill();
                } else if (type === 'square') {
                    ctx.lineWidth = 2 * scale;
                    ctx.beginPath();
                    ctx.rect(centerX - 5 * scale, centerY - 5 * scale, 10 * scale, 10 * scale);
                    ctx.stroke();
                }
                return;
            }

            const positions = getSurfacePosition(centerX, centerY, scale, surface);
            
            ctx.fillStyle = color;
            ctx.strokeStyle = color;

            if (type === 'dot') {
                ctx.beginPath();
                ctx.arc(positions.x, positions.y, 4 * scale, 0, 2 * Math.PI);
                ctx.fill();
            } else if (type === 'square') {
                ctx.lineWidth = 2 * scale;
                ctx.beginPath();
                ctx.rect(positions.x - 5 * scale, positions.y - 5 * scale, 10 * scale, 10 * scale);
                ctx.stroke();
            }
        };

        const drawSurfaceCondition = (ctx, centerX, centerY, scale, surface, color, type) => {
            // Special handling for cervical buccal decay
            if (surface === 'cervical_buccal' && color === '#E68074' && effectiveView.value === 'front') {
                ctx.save();
                ctx.globalCompositeOperation = 'source-atop';
                const rectHeight = 12 * scale;
                const y = ctx.canvas.height * 0.6 - rectHeight / 2;
                ctx.fillStyle = color;
                ctx.fillRect(0, y, ctx.canvas.width, rectHeight);
                ctx.restore();
                return;
            }

            // Special handling for occlusal and incisal surfaces in top view
            if ((surface === 'occlusal' || surface === 'incisal') && effectiveView.value === 'top') {
                drawOcclusalSurface(ctx, centerX, centerY, scale, color, type);
                return;
            }

            // Special handling for mesial and distal surfaces
            if (surface === 'mesial' || surface === 'distal') {
                drawProximalSurface(ctx, centerX, centerY, scale, surface, color, type);
                return;
            }

            // Default surface drawing
            const positions = getSurfaceArea(centerX, centerY, scale, surface);
            drawEllipticalSurface(ctx, positions, color, type, scale);
        };

        const drawOcclusalSurface = (ctx, centerX, centerY, scale, color, type) => {
            const width = ctx.canvas.width * 0.7;
            const height = 20 * scale;
            const adjustedCenterY = centerY + scale * 3;

            ctx.save();
            
            // Create organic shape path
            ctx.beginPath();
            const startX = centerX - width / 2;
            const endX = centerX + width / 2;
            const topY = adjustedCenterY - height / 2;
            const bottomY = adjustedCenterY + height / 2;
            
            // Draw organic tooth shape
            ctx.moveTo(startX, adjustedCenterY);
            ctx.quadraticCurveTo(startX + width * 0.15, topY - height * 0.3, startX + width * 0.25, topY);
            ctx.quadraticCurveTo(centerX, topY + height * 0.2, endX - width * 0.25, topY);
            ctx.quadraticCurveTo(endX - width * 0.15, topY - height * 0.3, endX, adjustedCenterY);
            ctx.quadraticCurveTo(endX - width * 0.15, bottomY + height * 0.3, endX - width * 0.25, bottomY);
            ctx.quadraticCurveTo(centerX, bottomY - height * 0.2, startX + width * 0.25, bottomY);
            ctx.quadraticCurveTo(startX + width * 0.15, bottomY + height * 0.3, startX, adjustedCenterY);
            ctx.closePath();
            
            if (type === 'fill') {
                ctx.globalCompositeOperation = 'source-atop';
                ctx.fillStyle = color;
                ctx.fill();
            } else if (type === 'pattern') {
                ctx.save();
                ctx.clip();
                ctx.strokeStyle = color;
                ctx.lineWidth = 1 * scale;
                const spacing = 4 * scale;
                for (let i = startX - height; i < endX + height; i += spacing) {
                    ctx.beginPath();
                    ctx.moveTo(i, topY - height);
                    ctx.lineTo(i + height * 2, bottomY + height);
                    ctx.stroke();
                }
                ctx.restore();
            }
            
            ctx.restore();
        };

        const drawProximalSurface = (ctx, centerX, centerY, scale, surface, color, type) => {
            const margin = 6 * scale;
            let rectW = 40 * scale;
            let rectH = 56 * scale;
            let radius = 10 * scale;
            let x, y, angle = 0;

            if (effectiveView.value === 'front') {
                x = surface === 'mesial' ? 
                    ctx.canvas.width - rectW / 2 + scale * 8 : 
                    rectW / 2 - scale * 8;
                y = ctx.canvas.height - rectH / 2 + scale * 8;
            } else if (effectiveView.value === 'top') {
                rectW = rectH = 60 * scale;
                radius = 20 * scale;
                x = surface === 'mesial' ? ctx.canvas.width : 0;
                y = centerY + 10 * scale;
                angle = surface === 'mesial' ? 
                    (64 * Math.PI) / 180 : 
                    -(64 * Math.PI) / 180;
            } else if (effectiveView.value === 'upside-down') {
                x = surface === 'mesial' ? 
                    ctx.canvas.width - rectW / 2 + scale * 8 : 
                    rectW / 2 - scale * 8;
                y = margin + rectH / 2 - scale * 14;
            }

            ctx.save();
            ctx.translate(x, y);
            if (angle) ctx.rotate(angle);

            // Draw rounded rectangle
            ctx.beginPath();
            const x0 = -rectW / 2, y0 = -rectH / 2;
            const x1 = x0 + rectW, y1 = y0 + rectH;
            const r = Math.min(radius, rectH / 2, rectW / 2);
            
            ctx.moveTo(x0 + r, y0);
            ctx.lineTo(x1 - r, y0);
            ctx.quadraticCurveTo(x1, y0, x1, y0 + r);
            ctx.lineTo(x1, y1 - r);
            ctx.quadraticCurveTo(x1, y1, x1 - r, y1);
            ctx.lineTo(x0 + r, y1);
            ctx.quadraticCurveTo(x0, y1, x0, y1 - r);
            ctx.lineTo(x0, y0 + r);
            ctx.quadraticCurveTo(x0, y0, x0 + r, y0);
            ctx.closePath();

            if (type === 'pattern') {
                ctx.save();
                ctx.clip();
                ctx.strokeStyle = color;
                ctx.lineWidth = 1 * scale;
                const spacing = 4 * scale;
                for (let i = -rectW * 2; i < rectW * 2; i += spacing) {
                    ctx.beginPath();
                    ctx.moveTo(i - rectW, -rectH * 2);
                    ctx.lineTo(i + rectW, rectH * 2);
                    ctx.stroke();
                }
                ctx.restore();
            } else {
                ctx.globalCompositeOperation = 'source-atop';
                ctx.fillStyle = color;
                ctx.fill();
            }

            ctx.restore();
        };

        const drawEllipticalSurface = (ctx, positions, color, type, scale) => {
            ctx.save();
            ctx.fillStyle = color;
            
            if (type === 'fill') {
                ctx.globalCompositeOperation = 'source-atop';
                ctx.beginPath();
                ctx.ellipse(positions.x, positions.y, positions.width/2, positions.height/2, 0, 0, 2 * Math.PI);
                ctx.fill();
            } else if (type === 'pattern') {
                ctx.strokeStyle = color;
                ctx.lineWidth = 1 * scale;
                ctx.globalCompositeOperation = 'source-atop';
                
                const spacing = 3 * scale;
                for (let i = -positions.width; i < positions.width; i += spacing) {
                    ctx.beginPath();
                    ctx.moveTo(positions.x + i - positions.width/2, positions.y - positions.height/2);
                    ctx.lineTo(positions.x + i + positions.width/2, positions.y + positions.height/2);
                    ctx.stroke();
                }
            }
            
            ctx.restore();
        };

        const drawToothWear = (ctx, centerX, centerY, scale, toothWear) => {
            const surfaces = toothWear.toothWearSurfaces || [];
            const toothWearType = (toothWear.toothWearType || 'abrasion').toLowerCase();

            // Front/top-specific wide area patterns
            if (toothWearType === 'abrasion') {
                if (surfaces.includes('buccal') && effectiveView.value === 'front') {
                    drawAbrasionPattern(ctx, centerY, scale, true);
                }
                if (surfaces.includes('palatal') && effectiveView.value === 'upside-down') {
                    drawAbrasionPattern(ctx, centerY, scale, false);
                }
            } else if (toothWearType === 'erosion') {
                // Handle top view separately: render from top->middle for buccal and bottom->middle for palatal
                if (effectiveView.value === 'top') {
                    if (surfaces.includes('buccal')) {
                        drawTopErosionBand(ctx, centerX, centerY, scale, 'buccal');
                    }
                    if (surfaces.includes('palatal')) {
                        drawTopErosionBand(ctx, centerX, centerY, scale, 'palatal');
                    }
                } else {
                    // Front & upside-down: new striped erosion band (and remove enamel portion)
                    if (surfaces.includes('buccal') && props.view === 'front') {
                        drawSideErosionBand(ctx, centerX, centerY, scale, true);
                    }
                    if (surfaces.includes('palatal') && props.view === 'upside-down') {
                        drawSideErosionBand(ctx, centerX, centerY, scale, false);
                    }
                }
            }

            // For all other surfaces draw a patterned overlay (e.g. incisal/occlusal/distal/etc.)
            surfaces.forEach(surface => {
                if ((surface === 'buccal' && effectiveView.value === 'front') ||
                    (surface === 'palatal' && effectiveView.value === 'upside-down')) {
                    return;
                }
                if (shouldDrawSurface(surface)) {
                    // Use an amber/orange pattern for wear indicators
                    drawSurfaceCondition(ctx, centerX, centerY, scale, surface, '#f39c12', 'pattern');
                }
            });
        };

        const drawAbrasionPattern = (ctx, centerY, scale, isFront) => {
            ctx.save();
            ctx.globalCompositeOperation = 'source-atop';
            
            const margin = 6 * scale;
            const rectHeight = 8 * scale;
            const x = margin;
            const w = Math.max(0, ctx.canvas.width - margin * 2);
            const y = isFront ? 
                centerY + rectHeight / 2 + ctx.canvas.height * 0.06 :
                centerY - rectHeight / 2 - ctx.canvas.height * 0.06;

            const grad = ctx.createLinearGradient(0, y - rectHeight * 0.5, 0, y + rectHeight * 1.5);
            grad.addColorStop(0, 'rgba(0,0,0,0)');
            grad.addColorStop(0.3, 'rgba(80,80,80,0.15)');
            grad.addColorStop(0.7, 'rgba(60,60,60,0.25)');
            grad.addColorStop(1, 'rgba(0,0,0,0)');

            ctx.fillStyle = grad;
            ctx.fillRect(x, y - rectHeight * 0.5, w, rectHeight * 2);
            ctx.restore();
        };

        // Erosion pattern: subtler, wider, smoother gradient than abrasion
        const drawErosionPattern = (ctx, centerY, scale, isFront) => {
            ctx.save();
            ctx.globalCompositeOperation = 'source-atop';

            const margin = 6 * scale;
            const rectHeight = 6 * scale; // slightly thinner
            const x = margin;
            const w = Math.max(0, ctx.canvas.width - margin * 2);
            const y = isFront ?
                centerY + rectHeight / 2 + ctx.canvas.height * 0.06 :
                centerY - rectHeight / 2 - ctx.canvas.height * 0.06;

            const grad = ctx.createLinearGradient(0, y - rectHeight, 0, y + rectHeight * 2);
            grad.addColorStop(0, 'rgba(0,0,0,0)');
            grad.addColorStop(0.25, 'rgba(120,120,120,0.08)');
            grad.addColorStop(0.5, 'rgba(120,120,120,0.14)');
            grad.addColorStop(0.75, 'rgba(120,120,120,0.08)');
            grad.addColorStop(1, 'rgba(0,0,0,0)');

            ctx.fillStyle = grad;
            ctx.fillRect(x, y - rectHeight * 0.5, w, rectHeight * 2);
            ctx.restore();
        };

        const drawFracture = (ctx, centerX, centerY, scale, fracture) => {
            if (!fracture.fractureOrientation) return;

            ctx.save();
            ctx.beginPath();
            ctx.rect(0, 0, ctx.canvas.width, ctx.canvas.height);
            ctx.clip();
            
            ctx.globalCompositeOperation = 'source-atop';
            ctx.strokeStyle = '#e74c3c';
            ctx.lineWidth = scale;
            ctx.beginPath();

            if (fracture.fractureType === 'root_fracture') {
                if (fracture.fractureOrientation === 'vertical' && effectiveView.value !== 'top') {
                    drawZigzagLine(ctx, centerX, centerY - ctx.canvas.height * 0.4, centerX, centerY + ctx.canvas.height * 0.4, scale, true);
                } else if (fracture.fractureOrientation === 'horizontal' && effectiveView.value !== 'top') {
                    drawZigzagLine(ctx, 0, centerY, ctx.canvas.width, centerY, scale, false);
                }
            } else if (fracture.fractureType === 'crown_fracture') {
                if (fracture.fractureOrientation === 'vertical' && effectiveView.value === 'top') {
                    drawZigzagLine(ctx, 0, centerY, ctx.canvas.width, centerY, scale, false);
                } else if (fracture.fractureOrientation === 'horizontal' && effectiveView.value !== 'top') {
                    const y = effectiveView.value === 'front' ? ctx.canvas.height * 0.8 : ctx.canvas.height * 0.2;
                    drawZigzagLine(ctx, 0, y, ctx.canvas.width, y, scale, false);
                }
            }
            
            ctx.stroke();
            ctx.restore();
        };

        const drawZigzagLine = (ctx, x1, y1, x2, y2, scale, isVertical) => {
            const segments = 8;
            const zigWidth = isVertical ? 4 * scale : 8 * scale;
            const zigHeight = isVertical ? 8 * scale : 4 * scale;

            if (isVertical) {
                const totalHeight = Math.abs(y2 - y1);
                const segmentHeight = totalHeight / segments;
                
                ctx.moveTo(x1, y1);
                for (let i = 0; i < segments; i++) {
                    const y = y1 + (i + 1) * segmentHeight;
                    const x = x1 + (i % 2 === 0 ? zigWidth : -zigWidth);
                    ctx.lineTo(x, y);
                }
                ctx.lineTo(x2, y2);
            } else {
                const totalWidth = Math.abs(x2 - x1);
                const segmentWidth = totalWidth / segments;
                
                ctx.moveTo(x1, y1);
                for (let i = 0; i < segments; i++) {
                    const x = x1 + (i + 1) * segmentWidth;
                    const y = y1 + (i % 2 === 0 ? zigHeight : -zigHeight);
                    ctx.lineTo(x, y);
                }
                ctx.lineTo(x2, y2);
            }
        };

        const drawDiscoloration = (ctx, centerX, centerY, scale, color) => {
            const colorMap = {
                gray: 'rgba(127, 140, 141, 0.3)',
                red: 'rgba(231, 76, 60, 0.3)',
                yellow: 'rgba(241, 196, 15, 0.3)',
                composite: 'rgba(131, 199, 231, 1)', // #83c7e7
                ceramic: 'rgba(177, 131, 232, 1)',   // #b183e8
                gold: 'rgba(250, 221, 99, 1)',       // #fadd63
                non_precious_metal: 'rgba(204, 207, 209, 1)', // #cccfd1
            };
            
            const fillColor = colorMap[color] || 'rgba(127, 140, 141, 0.3)';

            ctx.save();
            ctx.globalAlpha = 0.4;
            ctx.fillStyle = fillColor;
            ctx.globalCompositeOperation = 'source-atop';

            // For lower teeth we want the crown overlay to appear on the opposite
            // vertical side compared to upper teeth. Determine if this tooth is lower
            // and flip the front/upside-down branches so discoloration appears
            // on the crown (top) for lower teeth.
            const isLowerTooth = getToothType() === 'lower';

            // Decide which drawing branch to use. Keep top view unchanged.
            if (effectiveView.value === 'front') {
                if (isLowerTooth) {
                    // Draw like the 'upside-down' branch so the band is at the top
                    ctx.translate(0, -8 * scale);
                    ctx.beginPath();
                    ctx.moveTo(0, centerY);
                    ctx.quadraticCurveTo(centerX, centerY + 12 * scale, ctx.canvas.width, centerY);
                    ctx.lineTo(ctx.canvas.width, 0);
                    ctx.lineTo(0, 0);
                    ctx.closePath();
                    ctx.fill();
                } else {
                    // Default front behavior (band toward bottom)
                    ctx.translate(0, 14 * scale);
                    ctx.beginPath();
                    ctx.moveTo(0, centerY);
                    ctx.quadraticCurveTo(centerX, centerY + 12 * scale, ctx.canvas.width, centerY);
                    ctx.lineTo(ctx.canvas.width, ctx.canvas.height);
                    ctx.lineTo(0, ctx.canvas.height);
                    ctx.closePath();
                    ctx.fill();
                }
            } else if (effectiveView.value === 'top') {
                ctx.beginPath();
                ctx.rect(0, 0, ctx.canvas.width, ctx.canvas.height);
                ctx.fill();
            } else if (effectiveView.value === 'upside-down') {
                if (isLowerTooth) {
                    // For lower teeth, upside-down should appear like front (band at bottom)
                    ctx.translate(0, 14 * scale);
                    ctx.beginPath();
                    ctx.moveTo(0, centerY);
                    ctx.quadraticCurveTo(centerX, centerY + 12 * scale, ctx.canvas.width, centerY);
                    ctx.lineTo(ctx.canvas.width, ctx.canvas.height);
                    ctx.lineTo(0, ctx.canvas.height);
                    ctx.closePath();
                    ctx.fill();
                } else {
                    // Default upside-down behavior (band toward top)
                    ctx.translate(0, -8 * scale);
                    ctx.beginPath();
                    ctx.moveTo(0, centerY);
                    ctx.quadraticCurveTo(centerX, centerY + 12 * scale, ctx.canvas.width, centerY);
                    ctx.lineTo(ctx.canvas.width, 0);
                    ctx.lineTo(0, 0);
                    ctx.closePath();
                    ctx.fill();
                }
            }

            ctx.restore();
        };

        // Erosion-specific subtle discoloration overlay used for buccal/palatal
        const drawErosionDiscoloration = (ctx, centerX, centerY, scale, isFront) => {
            // Stronger cool gray translucent tint for erosion overlay (kept subtle)
            const fillColor = 'rgba(120,120,120,0.18)';

            ctx.save();
            // slightly higher alpha to improve visibility but remain natural
            ctx.globalAlpha = 0.75;
            ctx.fillStyle = fillColor;
            ctx.globalCompositeOperation = 'source-atop';

            if (isFront && effectiveView.value === 'front') {
                // buccal/front: a smooth quadratic band lower on the crown
                ctx.translate(0, 10 * scale);
                ctx.beginPath();
                ctx.moveTo(0, centerY - 4 * scale);
                ctx.quadraticCurveTo(centerX, centerY + 10 * scale, ctx.canvas.width, centerY - 4 * scale);
                ctx.lineTo(ctx.canvas.width, ctx.canvas.height);
                ctx.lineTo(0, ctx.canvas.height);
                ctx.closePath();
                ctx.fill();
            } else if (!isFront && effectiveView.value === 'upside-down') {
                // palatal/upside-down: mirror the band toward the top
                ctx.translate(0, -8 * scale);
                ctx.beginPath();
                ctx.moveTo(0, centerY + 4 * scale);
                ctx.quadraticCurveTo(centerX, centerY - 10 * scale, ctx.canvas.width, centerY + 4 * scale);
                ctx.lineTo(ctx.canvas.width, 0);
                ctx.lineTo(0, 0);
                ctx.closePath();
                ctx.fill();
            }

            // Add a slightly stronger textured pattern to suggest enamel loss
            ctx.globalAlpha = 0.35;
            ctx.save();
            ctx.clip();
            ctx.strokeStyle = 'rgba(255,255,255,0.10)';
            ctx.lineWidth = 1.2 * scale;
            const spacing = 5 * scale;
            for (let i = -ctx.canvas.width; i < ctx.canvas.width * 2; i += spacing) {
                ctx.beginPath();
                ctx.moveTo(i, ctx.canvas.height * 0.62);
                ctx.lineTo(i + ctx.canvas.width * 0.15, ctx.canvas.height * 0.52);
                ctx.stroke();
            }
            ctx.restore();

            ctx.restore();
        };

        // Top view: draw erosion band either from top -> middle (buccal) or bottom -> middle (palatal)
        const drawTopErosionBand = (ctx, centerX, centerY, scale, side) => {
            ctx.save();
            ctx.globalCompositeOperation = 'source-atop';

            const w = ctx.canvas.width;
            const h = ctx.canvas.height;
            const bandHeight = Math.max(6 * scale, h * 0.12);

            let y0, y1;
            if (side === 'buccal') {
            // top -> middle
            y0 = 0;
            y1 = centerY;
            } else {
            // palatal: bottom -> middle
            y0 = h;
            y1 = centerY;
            }

            // Create band path (same shape for fill and clipping)
            ctx.beginPath();
            if (side === 'buccal') {
            ctx.moveTo(0, y0);
            ctx.lineTo(w, y0);
            ctx.lineTo(w, Math.min(h, y1 + bandHeight));
            ctx.quadraticCurveTo(centerX, y1, 0, Math.min(h, y1 + bandHeight));
            ctx.closePath();
            } else {
            ctx.moveTo(0, y0);
            ctx.lineTo(w, y0);
            ctx.lineTo(w, Math.max(0, y1 - bandHeight));
            ctx.quadraticCurveTo(centerX, y1, 0, Math.max(0, y1 - bandHeight));
            ctx.closePath();
            }

            // Fill with a slightly stronger translucent overlay
            ctx.fillStyle = 'rgba(220,220,220,0.16)';
            ctx.fill();

            // Clip to the band and draw a zebra (alternating stripe) pattern
            ctx.save();
            ctx.clip();

            // Slight inner softening: draw a faint inner highlight to mimic the image's soft sheen
            ctx.globalAlpha = 0.18;
            ctx.fillStyle = 'rgba(255,255,255,0.06)';
            if (side === 'buccal') {
            ctx.fillRect(0, y0, w, Math.max(0, y1));
            } else {
            ctx.fillRect(0, Math.max(0, y1 - bandHeight * 2), w, h);
            }
            ctx.globalAlpha = 1;

            // Create an offscreen canvas to generate a zebra pattern (diagonal alternating stripes)
            const patternCanvas = document.createElement('canvas');
            const pSize = Math.max(24 * scale, 32); // pattern tile size
            patternCanvas.width = pSize;
            patternCanvas.height = pSize;
            const pctx = patternCanvas.getContext('2d');

            // Base fill (slightly transparent, increased)
            pctx.fillStyle = 'rgba(255,255,255,0.10)';
            pctx.fillRect(0, 0, pSize, pSize);

            // Draw diagonal zebra stripes by rotating the offscreen context
            pctx.save();
            pctx.translate(pSize / 2, pSize / 2);
            pctx.rotate(-Math.PI / 6); // -30 degrees to match original diagonal feel

            // Make stripes slightly wider and more visible
            const stripeWidth = Math.max(7 * scale, 7);
            const stripeGap = Math.max(6 * scale, 6);
            pctx.fillStyle = 'rgba(255,255,255,0.24)'; // brighter stripe color
            // draw vertical stripes on rotated context (they appear diagonal when used as pattern)
            for (let x = -pSize; x < pSize * 2; x += stripeWidth + stripeGap) {
            pctx.fillRect(x - stripeWidth / 2, -pSize * 2, stripeWidth, pSize * 4);
            }
            pctx.restore();

            // Create pattern and fill the clipped band
            const pattern = ctx.createPattern(patternCanvas, 'repeat');
            ctx.fillStyle = pattern;
            ctx.globalAlpha = 0.92; // more visible pattern
            ctx.fillRect(0, 0, w, h);
            ctx.globalAlpha = 1;

            // Add a faint inner edge (subtle shadow toward the middle) to enhance depth
            ctx.globalAlpha = 0.10;
            ctx.fillStyle = 'rgba(0,0,0,0.12)';
            if (side === 'buccal') {
            ctx.beginPath();
            ctx.moveTo(0, y1);
            ctx.quadraticCurveTo(centerX, y1 - bandHeight * 0.4, w, y1);
            ctx.lineTo(w, Math.min(h, y1 + bandHeight));
            ctx.lineTo(0, Math.min(h, y1 + bandHeight));
            ctx.closePath();
            } else {
            ctx.beginPath();
            ctx.moveTo(0, y1);
            ctx.quadraticCurveTo(centerX, y1 + bandHeight * 0.4, w, y1);
            ctx.lineTo(w, Math.max(0, y1 - bandHeight));
            ctx.lineTo(0, Math.max(0, y1 - bandHeight));
            ctx.closePath();
            }
            ctx.fill();
            ctx.globalAlpha = 1;

            ctx.restore(); // restore clipping and transforms
            ctx.restore(); // final restore for composite operation
        };

        // Front / Upside-down erosion striped band for buccal & palatal surfaces
        const drawSideErosionBand = (ctx, centerX, centerY, scale, isFront) => {
            ctx.save();

            const w = ctx.canvas.width;
            const h = ctx.canvas.height;

            // Build crown area path similar to discoloration overlay
            ctx.beginPath();
            ctx.globalCompositeOperation = 'source-atop';
            ctx.fillStyle = 'rgba(180,180,180,0.10)';
            if (isFront) {
                ctx.save();
                ctx.translate(0, 14 * scale); // mimic discoloration shift
                ctx.moveTo(0, centerY);
                ctx.quadraticCurveTo(centerX, centerY + 12 * scale, w, centerY);
                ctx.lineTo(w, h);
                ctx.lineTo(0, h);
                ctx.closePath();
                // Clip area
                ctx.clip();
                ctx.fillRect(0, centerY - 5 * scale, w, h - centerY + 20 * scale);
                // Base translucent fill
                ctx.fillRect(0, centerY - 5 * scale, w, h - centerY + 20 * scale);
            } else { // upside-down / palatal
                ctx.save();
                ctx.translate(0, -8 * scale); // mimic upside-down discoloration shift
                ctx.moveTo(0, centerY);
                ctx.quadraticCurveTo(centerX, centerY + 12 * scale, w, centerY);
                ctx.lineTo(w, 0);
                ctx.lineTo(0, 0);
                ctx.closePath();
                ctx.clip();
                ctx.fillRect(0, 0, w, centerY + 15 * scale);
            }

            // Draw diagonal stripe pattern (reuse logic from top pattern)
            const patternCanvas = document.createElement('canvas');
            const pSize = Math.max(24 * scale, 32);
            patternCanvas.width = pSize;
            patternCanvas.height = pSize;
            const pctx = patternCanvas.getContext('2d');
            pctx.fillStyle = 'rgba(255,255,255,0.04)';
            pctx.fillRect(0, 0, pSize, pSize);
            pctx.save();
            pctx.translate(pSize / 2, pSize / 2);
            pctx.rotate(-Math.PI / 6);
            const stripeWidth = Math.max(6 * scale, 6);
            const stripeGap = stripeWidth;
            pctx.fillStyle = 'rgba(255,255,255,0.18)';
            for (let x = -pSize; x < pSize * 2; x += stripeWidth + stripeGap) {
                pctx.fillRect(x - stripeWidth / 2, -pSize * 2, stripeWidth, pSize * 4);
            }
            pctx.restore();
            const pattern = ctx.createPattern(patternCanvas, 'repeat');
            ctx.globalAlpha = 0.9;
            ctx.fillStyle = pattern;
            if (isFront) {
                ctx.fillRect(0, centerY - 5 * scale, w, h - centerY + 20 * scale);
            } else {
                ctx.fillRect(0, 0, w, centerY + 15 * scale);
            }
            ctx.globalAlpha = 1;

            // Subtle internal shadow/highlight for depth
            ctx.globalAlpha = 0.08;
            ctx.fillStyle = 'rgba(0,0,0,0.35)';
            if (isFront) {
                ctx.beginPath();
                ctx.moveTo(0, centerY + 12 * scale);
                ctx.quadraticCurveTo(centerX, centerY + 4 * scale, w, centerY + 12 * scale);
                ctx.lineTo(w, h);
                ctx.lineTo(0, h);
                ctx.closePath();
                ctx.fill();
            } else {
                ctx.beginPath();
                ctx.moveTo(0, centerY - 12 * scale);
                ctx.quadraticCurveTo(centerX, centerY - 4 * scale, w, centerY - 12 * scale);
                ctx.lineTo(w, 0);
                ctx.lineTo(0, 0);
                ctx.closePath();
                ctx.fill();
            }
            ctx.globalAlpha = 1;

            ctx.restore(); // inner translated save
            ctx.restore(); // outer
        };

        const drawApicalPathology = (ctx, centerX, centerY, scale) => {
            ctx.save();
            ctx.globalAlpha = 0.3;
            ctx.fillStyle = '#34495e';
            ctx.globalCompositeOperation = 'source-atop';
            ctx.beginPath();
            ctx.arc(centerX, centerY + 15 * scale, 8 * scale, 0, 2 * Math.PI);
            ctx.fill();
            ctx.restore();
        };

        const drawRootCanal = (ctx, centerX, centerY, scale) => {
            ctx.strokeStyle = '#8e44ad';
            ctx.lineWidth = 3 * scale;
            ctx.beginPath();
            ctx.moveTo(centerX, centerY + 5 * scale);
            ctx.lineTo(centerX, centerY + 20 * scale);
            ctx.stroke();
            
            ctx.fillStyle = '#8e44ad';
            ctx.beginPath();
            ctx.arc(centerX, centerY, 2 * scale, 0, 2 * Math.PI);
            ctx.fill();
        };

        const drawImplant = (ctx, centerX, centerY, scale) => {
            ctx.fillStyle = '#34495e';
            ctx.beginPath();
            ctx.rect(centerX - 3 * scale, centerY - 15 * scale, 6 * scale, 30 * scale);
            ctx.fill();
            
            ctx.strokeStyle = '#2c3e50';
            ctx.lineWidth = 1 * scale;
            for (let i = -10; i <= 10; i += 5) {
                ctx.beginPath();
                ctx.moveTo(centerX - 3 * scale, centerY + i * scale);
                ctx.lineTo(centerX + 3 * scale, centerY + i * scale);
                ctx.stroke();
            }
        };

        const drawToBeExtractedBorder = (ctx) => {
            if (!props.tooth.conditions?.to_be_extracted) return;

            const canvas = toothCanvas.value;
            ctx.save();
            ctx.strokeStyle = 'red';
            ctx.lineWidth = 6;
            ctx.setLineDash([20, 20]);
            ctx.strokeRect(0, 0, canvas.width, canvas.height);
            ctx.restore();
        };

        const drawVeneerPalatal = (ctx, centerX, centerY, scale, color) => {
            ctx.save();
            ctx.fillStyle = color;
            ctx.globalCompositeOperation = 'source-atop';

            if (effectiveView.value === 'upside-down') {
                // Fill entire crown in upside-down view with a subtle curved bottom edge
                const w = ctx.canvas.width;
                const h = ctx.canvas.height;
                const baseY = h * 0.45;
                const curveDepth = Math.max(24 * scale, 4); // slight bulge amount
                const inset = 2 * scale;

                ctx.beginPath();
                ctx.moveTo(0, 0);
                ctx.lineTo(w, 0);
                // go down to just above the base line on the right
                ctx.lineTo(w, baseY - inset);
                // curve the bottom edge inward a bit using a quadratic curve
                ctx.quadraticCurveTo(w / 2, baseY + curveDepth, 0, baseY - inset);
                ctx.closePath();
                ctx.fill();
            } else if (effectiveView.value === 'top') {
                // 3/4 from bottom to top
                const startY = ctx.canvas.height * 0.25; // Start from 1/4 from top
                ctx.beginPath();
                ctx.rect(0, startY, ctx.canvas.width, ctx.canvas.height - startY);
                ctx.fill();
            } else if (effectiveView.value === 'front') {
                // 1/4 of crown from bottom to top
                const startY = ctx.canvas.height * 0.80; // Start from 3/4 down
                ctx.beginPath();
                ctx.rect(0, startY, ctx.canvas.width, ctx.canvas.height - startY);
                ctx.fill();
            }

            ctx.restore();
        };

        const drawVeneerBuccal = (ctx, centerX, centerY, scale, color) => {
            ctx.save();
            ctx.fillStyle = color;
            ctx.globalCompositeOperation = 'source-atop';

            if (effectiveView.value === 'front') {
                // Start from bottom, similar to palatal front view: fill a lower band with a subtle curved top
                const h = ctx.canvas.height;
                const startY = h * 0.60; // start at 80% down (fill bottom 20%)
                const curveDepth = Math.max(8 * scale, 4);

                ctx.beginPath();
                // Create a slightly curved top edge for a more natural veneer look
                ctx.moveTo(0, startY);
                ctx.quadraticCurveTo(ctx.canvas.width / 2, startY - curveDepth, ctx.canvas.width, startY);
                ctx.lineTo(ctx.canvas.width, h);
                ctx.lineTo(0, h);
                ctx.closePath();
                ctx.fill();
            } else if (effectiveView.value === 'top') {
                // 3/4 from top to bottom (opposite direction from palatal)
                const endY = ctx.canvas.height * 0.75; // End at 3/4 from top
                ctx.beginPath();
                ctx.rect(0, 0, ctx.canvas.width, endY);
                ctx.fill();
            } else if (effectiveView.value === 'upside-down') {
                // 1/4 of crown from top to bottom (opposite of palatal)
                const endY = ctx.canvas.height * 0.25; // End at 1/4 from top
                ctx.beginPath();
                ctx.rect(0, 0, ctx.canvas.width, endY);
                ctx.fill();
            }

            ctx.restore();
        };

        // Main canvas loading function
        const loadImageToCanvas = async () => {
            if (!toothCanvas.value) return;

            const canvas = toothCanvas.value;
            const ctx = canvas.getContext('2d');
            
            const type = getToothType();
            const viewSuffix = getViewSuffix();
            const variant = getImageVariant();
            
            // Try cached image first for instant loading
            const cachedImage = getImageFromCache(type, props.tooth.id, viewSuffix, variant);
            
            if (cachedImage) {
                drawImageToCanvas(cachedImage, canvas, ctx);
                return;
            }

            // Show loading state
            ctx.fillStyle = '#f8f9fa';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Load and cache image
            try {
                const img = await loadAndCacheImage(type, props.tooth.id, viewSuffix, variant, toothImageUrl.value);
                if (img && toothCanvas.value) {
                    drawImageToCanvas(img, canvas, ctx);
                } else {
                    await loadFallbackImage(canvas, ctx);
                }
            } catch (error) {
                console.warn('Error loading tooth image:', error);
                await loadFallbackImage(canvas, ctx);
            }
        };

        const handleClick = () => {
            emit("tooth-clicked", props.tooth);
        };

        // Preload images for performance
        const preloadToothImages = () => {
            const id = props.tooth.id;
            const type = getToothType();
            
            // Preload missing variant
            const missingVariant = '-missing';
            if (!getImageFromCache(type, id, '', missingVariant)) {
                const missingUrl = getToothImageUrl(type, id, '', missingVariant);
                loadAndCacheImage(type, id, '', missingVariant, missingUrl);
            }
            
            // Preload implant variant
            const implantVariant = '-implant';
            if (!getImageFromCache(type, id, '', implantVariant)) {
                const implantUrl = getToothImageUrl(type, id, '', implantVariant);
                loadAndCacheImage(type, id, '', implantVariant, implantUrl);
            }
            
            // Preload upside-down implant variant
            if (!getImageFromCache(type, id, '-ud', implantVariant)) {
                const implantUdUrl = getToothImageUrl(type, id, '-ud', implantVariant);
                loadAndCacheImage(type, id, '-ud', implantVariant, implantUdUrl);
            }
            
            // Preload normal variant
            if (!getImageFromCache(type, id, '', '')) {
                const normalUrl = getToothImageUrl(type, id, '', '');
                loadAndCacheImage(type, id, '', '', normalUrl);
            }
        };

        // Watchers
        watch(() => props.tooth, () => {
            nextTick(() => {
                loadImageToCanvas();
            });
        }, { deep: true });
        
        watch(toothImageUrl, () => {
            nextTick(() => {
                loadImageToCanvas();
            });
        });
        
        watch(() => props.size, () => {
            nextTick(() => {
                loadImageToCanvas();
            });
        });

        onMounted(() => {
            loadImageToCanvas();
            preloadToothImages();
        });

        return {
            toothCanvas,
            toothClasses,
            toothStyle,
            canvasClasses,
            handleClick,
        };
    },
};
</script>

<style scoped>
.tooth {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: all 0.2s ease-in-out;
    border: 2px solid transparent;
    border-radius: 2px;
    margin: 0 3px;
    cursor: pointer;
    box-sizing: border-box;
}

.tooth.selected {
    border-color: #1890ff;
    background-color: rgba(24, 144, 255, 0.1);
    transform: scale(1.1);
}

.tooth-canvas {
    width: 100%;
    height: auto;
    z-index: 1;
    display: block;
}

.tooth.missing {
    opacity: 0.6;
}

.tooth.pontic {
    opacity: 0.7;
    background-color: rgba(200, 200, 200, 0.1);
}

.tooth-canvas.top-view {
    transform: none;
}
</style>
