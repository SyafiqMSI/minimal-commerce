<script lang="ts" setup>
    interface Props {
      img: string;
      name: string;
      username: string;
      body: string;
    }
    
    defineProps<Props>();
    </script>
    
    <template>
      <figure
        class="relative w-64 min-h-[120px] flex-shrink-0 cursor-pointer overflow-hidden rounded-xl border border-border/50 bg-card p-4 hover:bg-accent/50 hover:shadow-lg hover:shadow-primary/5 transition-all duration-300"
      >
        <div class="flex flex-row items-center gap-2">
          <img
            :src="img"
            class="rounded-full"
            width="32"
            height="32"
            alt=""
          />
          <div class="flex flex-col">
            <span class="text-sm font-medium text-foreground">
              {{ name }}
            </span>
            <p class="text-xs font-medium text-muted-foreground">{{ username }}</p>
          </div>
        </div>
        <blockquote class="mt-2 text-sm text-foreground/80">{{ body }}</blockquote>
      </figure>
    </template>
    
    